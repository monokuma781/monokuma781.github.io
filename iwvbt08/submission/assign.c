/****************************************************************************
   The MyReview system for web-based conference management
 
   Copyright (C) 2003-2004 Philippe Rigaux
   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation;
 
   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.
 
   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 
   Assignment Problem of Papers to Referees in a Conference
   The solution is based on the Weighted Matching Problem
   for a (complete) bipartite graph

   Usage: assign  [options]
   Options:   -o <filename>
                    write the result in <filename>
              -commit
                    write the result in the database. Important: 
                    this replaces the current assignment      
              -l -p -s -db for login, password, server and db name
   
   Input:  A list of reviewers, a list of papers, the number
           of reviewers per paper 
  	   A matrix reviewers \times papers
             with preferences (0=lowest, 4=highest) of reviewers for papers
          All inputs are read from the database
 
  Output: Max number of papers assigned to each referee, followed by a colon
          First part:
          Assignment of the Papers to the Referees maximizing Preferences
          Second part:
          Assignment of Referees to the Papers

   Remark: Optimizes number of papers for each referee;
           Number of referees per paper may wary.

   Authors: Miki Hermann (Miki.Hermann AT lix.polytechnique.fr)
           Julien Demouth (julien.demouth AT wanadoo.fr)
           Philippe Rigaux (rigaux AT lri.fr)
 
****************************************************************************/

#include <stdio.h>
#include <stdlib.h>

/* Include from /usr/local/mysql/include/  */
#include "mysql/mysql.h"

#define FALSE 0
#define TRUE  1

/* Global variables */

/********** Connection to MySQL: change the default values,
	    or override them with the -l -p, -db and -h options *************/
   
static char login[60]="adminReview", 
  password[60]="mdpAdmin", 
  dbname[60]="Review",
  server[60]="localhost";

/***************** End of MySQL connection variables ************/

static  int *db_papers;   /* Array of papers id, from the database */
static  char **db_emails; /* Array of members' email, from the DB */

static char output_file[256] = "assignment";
static int commit = FALSE, write_in_file=FALSE;

static int n;			/* Number of nodes on one side.
				   Determined by npapers.
				*/
static int improving = TRUE;    /* Boolean variable */

static int **weight;	  /* Positive weights on edges: 0 = edge absent 
			     Originally an array weight[][] where:
			     first coordinate is the left-hand side node,
			     second coordinate is the right-hand side node,
			     and weight[i][j] is the weight of the edge
			     between the nodes i and j.
			  */
static int *Lmate;        /* Mate of left-hand node */
static int *Rmate;        /* Mate of right-hand node */
static int *cost;         /* Cost function */
static int *pred;         /* Predecessor pointer */
static int **choice;	  /* Choice following iterated weighted matching */
static int *papers;	  /* Each paper chosen x times */

static int nrefs;	  /* Number of Referees */
static int npapers;	  /* Number of Papers */
static int papforref;	  /* Maximal number of papers for a referee */
static int refperpap;	  /* Number of Referees per Paper */
static int wsum;	  /* Sum of the weighted matching */

/*************** MySQL functions *******************/

int GetNbRevPerPaper (MYSQL *conn)
{
  MYSQL_RES *res;
  MYSQL_ROW row;
  int papforref;
  char query[] = "SELECT nbReviewersPerItem FROM Config";

  /* Execute the query   */
  mysql_query (conn, query);
  res = mysql_store_result (conn);
  if (res)
    {
      row = mysql_fetch_row (res);
      papforref = atoi(row[0]);
      mysql_free_result(res);
      return papforref;
    }
  else
    {
      printf ("MySQL error during execution of %s: %s\n", query,
	      mysql_error(conn));
      exit(1);
    }
}

int* GetPapers (MYSQL *conn, int *nb_papers)
{
  MYSQL_RES *res;
  MYSQL_ROW row;
  int i;
  int *papers;
  char query[] = "SELECT id FROM Paper";

  /* Get the list of papers   */
  mysql_query (conn, query);
  res = mysql_store_result (conn);
  if (res)
    {
      *nb_papers = mysql_num_rows(res);
      papers = (int*) malloc (sizeof(int) * *nb_papers);
      i = 0;
      while ( (row = mysql_fetch_row (res)))
	{
	  papers[i++] = atoi(row[0]);
	}
      mysql_free_result(res);
      return papers;
    }
  else
    {
      printf ("MySQL error during execution of %s: %s\n", query,
	      mysql_error(conn));
      exit(1);
    }
}

char** GetEmails (MYSQL *conn, int *nb_emails)
{
  MYSQL_RES *res;
  MYSQL_ROW row;
  int i;
  char** emails;
  char query[] = "SELECT email FROM PCMember";

  /* Execute the query   */
  mysql_real_query (conn, query, strlen(query));
  res = mysql_store_result (conn);
  if (res)
    {
      *nb_emails = mysql_num_rows(res);
      emails = (char**) malloc (sizeof(char*) * (*nb_emails));
      i = 0;
      while ( (row = mysql_fetch_row (res)))
	{
	  emails[i] = (char*) malloc(strlen(row[0]) + 1);
	  strcpy(emails[i], row[0]);
	  i++;
	}
      mysql_free_result(res);
      return emails;
    }
  else
    {
      printf ("MySQL error during execution of %s: %s\n", query,
	      mysql_error(conn));
      exit(1);
    }
}

int GetPreference (MYSQL *conn, int id_paper, char* email)
{
  MYSQL_RES *res;
  MYSQL_ROW row;
  int rate;
  char query[256];

  sprintf (query, "SELECT rate FROM Rating WHERE idPaper=%d AND email='%s'",
	   id_paper, email);

  mysql_query (conn, query);
  res = mysql_store_result (conn);
  if (res)
    {
      row = mysql_fetch_row (res);
      if (row == NULL)
	{
	  rate = 2; /* The default value */
	}
      else
	{
	  rate =  atoi(row[0]);
	}
      mysql_free_result(res);
      return rate;
    }
  else
    {
      printf ("MySQL error during execution of %s: %s\n", query,
	      mysql_error(conn));
      exit(1);
    }
}

int CommitAssignment (MYSQL *conn)
{
  MYSQL_RES *res;
  int rate;
  char query[256];

  char qSel[]="SELECT * FROM Review WHERE idPaper='%d' and email='%s'";
  char qIns[]="INSERT INTO Review (idPaper, email) VALUES('%d', '%s')";

  int i,j;
  int *iref;;
  int left;
  int pap;

  /* Delete all current assignments (excepts those which are already
     submitted */
  
  mysql_query(conn, "DELETE FROM Review WHERE submissionDate IS NULL");

  /* Referees */
  for (i = 1; i <= nrefs; i++)
    {
      iref = choice[i];
      
      for (j = 1; j <= npapers; j++)
	{
	  left = iref[j];
	  if (left > 0) {
	    /* Check that the review does not exists */
	    sprintf (query, qSel, db_papers[j-1], db_emails[i-1]);
	    mysql_query(conn, query);
	    res = mysql_store_result (conn);	   
	    if (mysql_fetch_row (res) == NULL)
	      {
		/* OK, insert  */
		sprintf (query, qIns, db_papers[j-1], db_emails[i-1]);
		mysql_query (conn, query);
	      }
	    else /* Row exists */;
	  }
	}
    }
}

/****************************************************************************/

void augment(int ww)
{
  int u,v,w;
  
  w = ww;
  u = *(pred + w);
  
  while (*(Lmate + u) != 0)		/* while u is not start vertex */
    {
      v = *(Lmate + u);
      *(Lmate + u) = w;		/* {u,w} added to matching,
				   {u,v} removed from matching */
      *(Rmate + v) = 0;		/* {u,v} removed on the other side */
      *(Rmate + w) = u;		/* doubling it on the other side */
      w = v;
      u = *(pred + w);
    }
  *(Lmate + u) = w;
  *(Rmate + w) = u;
}

void matching()
{
  int k, r, s, i;
  int extra;
  int C;
  int w;
  int rmts;
  int *wr;

  for (k = 1; k <= n; k++)
    {
      /* initialization - alternating paths of length 1 */

      for (r = 1; r <= n; r++)
	{
	  *(cost + r) = 0;		/* dummy value, will be overwritten
					   as G is complete */
	  for (s = 1; s <= n; s++)
	    if ((*(Lmate + s) == 0) && (*(*(weight + s) + r) > *(cost + r)))
	      {
		*(cost + r) = *(*(weight + s) + r);
		*(pred + r) = s;		/* denotes predecessor of w_r
					   on path so far */
	      }
	}

      /* allow alternating paths of length 3, 5, ..., 2k + 1 */

      improving = TRUE;

      for (i = 2; (i <= k) && improving; i++)
	{
	  improving = FALSE;

	  for (r = 1; r <= n; r++)
	    for (s = 1; s <= n; s++)
	      if ((r != s) && (*(Rmate + s) > 0))
		{
		  rmts = *(Rmate + s);
		  wr = *(weight + rmts);
		  extra = *(wr + r) - *(wr + s);

		  if (*(cost + s) + extra > *(cost + r))
		    {
		      *(cost + r) = *(cost + s) + extra;
		      *(pred + r) = *(Rmate + s);
		      improving = TRUE;
		    }
		}
	}

      /* choose exposed w with maximum cost */

      C = 0;
      for (r = 1; r <= n; r++)
	if((*(Rmate + r) == 0) && (*(cost + r) > C))
	  {
	    C = *(cost + r);
	    w = r;
	  }
      /* augmenting path ending at w is optimal */

      if (C > 0)
	augment(w);
      else
	improving = FALSE;
    }
}

/**************** Read the values from the database    *********/
void readin(MYSQL *conn)
{
  int i,j;
  int papref;

  /* Connect to MySQL  */
  mysql_init (conn);
  if (!mysql_real_connect (conn, server, login, password, dbname, 0, NULL, 0))
    {
      printf ("Unable to connect to MySQL database %s: %s\n", 
	      dbname, mysql_error(conn));
      exit(1);
    }
    

  /* Get the nb of reviewers per paper */
  refperpap = GetNbRevPerPaper (conn);

  /* Get the list of papers   */
  db_papers = GetPapers(conn, &npapers);
  /* Get the emails of PCMembers   */
  db_emails = GetEmails (conn, &nrefs);

	
  n = npapers;		/* Number of papers dominates number of referees */

  /* How many papers for each referee ?   */
  papref= npapers * refperpap;
  papforref = papref / nrefs;
  if ((papref % nrefs) != 0)
    papforref++;

  
  /* allocate and initialize internal structures */
  weight = calloc(n + 1, sizeof(int));
  for (i = 1; i <= n; i++)
    *(weight + i) = calloc(n + 1, sizeof(int));

  choice = calloc(nrefs + 1, sizeof(int));
  for (i = 1; i <= nrefs; i++)
    *(choice + i) = calloc(npapers + 1, sizeof(int));

  Lmate = calloc(n + 1, sizeof(int));
  Rmate = calloc(n + 1, sizeof(int));
  cost = calloc(n + 1, sizeof(int));
  pred = calloc(n + 1, sizeof(int));

  papers = calloc(npapers + 1, sizeof(int));

  /* read preferences */
  for (i = 1; i <= nrefs; i++)	     /* preferences of a referee i */
    for (j = 1; j <= npapers; j++)   /* preference of referee i for paper j */
      {
	weight[i][j] = 
	  GetPreference (conn, db_papers[j-1], db_emails[i-1]);
      }
}

void writeout(FILE *out)
{
  int i,j;
  int *iref;;
  int left;
  int pap;

  /* Found choice matrix */

  /* Referees */

  for (i = 1; i <= nrefs; i++)
    {
      iref = choice[i];
      
      for (j = 1; j <= npapers; j++)
	{
	  left = iref[j];
	  if (left > 0)
	    {
	      fprintf(out, "%s ; %d ;  %d\n", 
		     db_emails[i-1], db_papers[j-1], left);
	    }
	}
      fprintf(out, "\n");
    }
}

void init()
{
  int i;

  for (i = 1; i <= n; i++)
    {
      *(Lmate + i) = 0;
      *(Rmate + i) = 0;
      *(pred + i)  = 0;
    }
}

void takeout()
{
  int i, j, k;
  int cst;

  wsum = 0;

  for (i = 1; i <= nrefs; i++)
    {
      j = *(Lmate + i);
      if (j > 0)
	{
	  cst = *(*(weight + i) + j);
	  *(*(choice + i) + j) = cst;
	  wsum = wsum + cst;

	  /* if the number of referees for paper j reached the required
	     limit, then disable any other assignments of this paper
	  */

	  if (++(*(papers + j)) == refperpap)
	    for (k = 1; k <= nrefs; k++)
	      *(*(weight + k) + j) = 0;

	  *(*(weight + i) + j) = 0;
	}
    }
}

void release()
{
  int i;

  for (i = 1; i <= n; i++)
    free(*(weight + i));
  free(weight);

  for (i = 1; i <= nrefs; i++)
    free(*(choice + i));
  free(choice);

  free(Lmate);
  free(Rmate);
  free(cost);
  free(pred);

  free(papers);
}

int HandleArgs(int iargs, char **args)
{
  int i, correct=TRUE;

  for (i = 1; i < iargs; i++)
  {
    if (!strcmp(args[i], "-o"))
      {
	if (i + 1 < iargs) strcpy (output_file, args[i+1]);
	else correct=FALSE;
	write_in_file = TRUE;
      }

    if (!strcmp(args[i], "-l"))
      {
	if (i + 1 < iargs) strcpy (login, args[i+1]);
	else correct=FALSE;
      }
    if (!strcmp(args[i], "-s"))
      {
	if (i + 1 < iargs) strcpy (server, args[i+1]);
	else correct=FALSE;
      }
    if (!strcmp(args[i], "-p"))
      {
	if (i + 1 < iargs) strcpy (password, args[i+1]);
	else correct=FALSE;
      }
    if (!strcmp(args[i], "-db"))
      {
	if (i + 1 < iargs) strcpy (dbname, args[i+1]);
	else correct=FALSE;
      }

    if (!strcmp(args[i], "-commit"))
      {
	commit = TRUE;
      }
    if (!strcmp(args[i], "-h"))	correct=FALSE;
  }
  
  /* Better write in a file  */
  if (commit && write_in_file) commit = FALSE;
  if (!commit && !write_in_file) write_in_file = TRUE;
  
  if (!correct)
    {
      printf ("Usage: assign [-h] [-l login] [-p password] [-s server] [-db dbname] [-l filename | -commit]\n\n");
      exit(1);
    }
  else
    return 1;
}
/******************************************
               
            Main function

******************/

int main(int iargs, char* args[])
{
  int i;
  FILE *out;
  char line[255], login[55], yesno='n';
  MYSQL conn;

  /* read the command-line arguments */
  HandleArgs(iargs, args);

  if ( (out = fopen (output_file, "w")) == NULL)
    {
      printf ("Unable to open file %s\n", output_file);
      exit(1);
    }

  /* Read data */
  printf("Read input data from the database\n");
  readin(&conn);
  
  /*  for (i=0; i< npapers; i++) printf ("Paper id = %d\n", db_papers[i]); */
  printf ("Done\n");
  printf ("\tNumber of papers=%d\n", npapers);

  /* for (i=0; i< nrefs; i++) printf ("Email = %s\n", db_emails[i]);    */
  printf ("\tNumber of reviewers=%d\n", nrefs);
  printf ("\tNumber of reviewers per paper=%d\n", refperpap);
  printf("\tEach referee will get at most %d papers\n\n", papforref);

  printf("Compute the assignment\n");
  /* Compute the assignment  */
  wsum = 1;		/* dummy for first iteration */
  
  /*  for (i = 1; (i <= papforref) && (wsum > 0); i++) 
      Priority: number of rev. per paper, not the numer of papers per rev.!
  */
  for (i = 1; wsum > 0; i++)
    {
      init();
      matching();
      takeout();
    }
  printf ("Done!\n\n");
  /* Write out the result    */
  if (write_in_file)
    {
      printf ("Write the result in %s\n", output_file);
      writeout(out);
      printf ("Done\n");
    }
  else
    {
      printf ("Commit the result in the MySQL Database %s\n", dbname);
      printf ("Do you confirm (this will erase the current assignment) y/n [n]? ");
      fscanf (stdin, "%c", &yesno);
      if (yesno != 'y')
	{
	  printf ("Commit cancelled\n");
	}
      else
	{
	  CommitAssignment (&conn);
	  printf ("Commit confirmed\n");
	}
    }
  /* Release all allocations */
  release();
}
/* +++++++++++++++++++++++ end of asgn2.c +++++++++++++++++++++++ */

