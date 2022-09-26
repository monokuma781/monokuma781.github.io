<?php
/**********************************************
   The MyReview system for web-based conference management
 
   Copyright (C) 2003-2006 Philippe Rigaux
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
************************************************/
 
 
  // Compute the prediction for a paper and a user

  function ComputePrediction ($db)
  {
    // Do not take users which are not closely correlated
    define ("THRESHOLD_CORRELATION", 0.6);
    // Optimal number of co-rated papers
    define ("OPT_CO_RATED_PAPERS", 6);
 
    // First compute correlations and store them in the table
    // Nested loop on PC members
    $qPC = "SELECT * FROM PCMember";
    $rPCM1 = $db->execRequete($qPC);
    while ($PCM1 = $db->objetSuivant($rPCM1))
      {
	$qPC2 = "SELECT * FROM PCMember WHERE email!='$PCM1->email'";

	$rPCM2 = $db->execRequete($qPC2);
	while ($PCM2 = $db->objetSuivant($rPCM2))
	    ComputeCorrelation($PCM1->email, $PCM2->email, $db);
      }

    // Next compute predictions and store them as well

    // Loop on PC members
    $qPC = "SELECT * FROM PCMember";
    $rPCM = $db->execRequete($qPC);
    while ($PCM = $db->objetSuivant($rPCM))
      {
	$pcmAvg =  AvgUserRating ($PCM->email, $db);

	// Loop on papers
	$qPaper = "SELECT * FROM Paper";
	$rPaper = $db->execRequete ($qPaper);
	while ($paper = $db->objetSuivant($rPaper))
	  {
	    // Check that the current user has not explicitely 
	    // rated the paper
	    $myRate = GetRating($paper->id, $PCM->email, $db);
	    if (!is_object ($myRate) or $myRate->significance!=1)
	      {
		// Loop on all the other user ratings for the same paper
		$qRating = "SELECT * FROM Rating WHERE idPaper='$paper->id' "
		   . "AND email!='$PCM->email' AND rate!=0 AND significance=1";
		$rRating  = $db->execRequete ($qRating);
	    
		// echo "MyUser=$PCM->email MyAvg=$pcmAvg <br>";
		$sumCorr = 0.;
		$weightedSum = 0.;
		$sumSignificance = $nbRatings = 0;
		while ($rating =  $db->objetSuivant ($rRating))
		  {
		    $avgOther =  AvgUserRating($rating->email, $db);
		    $correlation = 
		       GetCorrelation ($PCM->email, $rating->email, $db);

		    // Maybe use a threshold for correlation
		    if ($correlation->correlation > THRESHOLD_CORRELATION)
		      {
			$sumCorr += (float)  $correlation->correlation;
			$weightedSum += (float) $correlation->correlation * 
			   ($rating->rate - $avgOther);
			$sumSignificance += 
			   (float) $correlation->nbCoRated 
			   / (float) OPT_CO_RATED_PAPERS;
			$nbRatings++;
			// echo "Corr. avec $rating->email: $correlation->correlation avg=$avgOther<br>";
		      }
		  }

		if ($sumCorr != 0.)   // Insert the result
		  {
		    $prediction = $pcmAvg + ($weightedSum / $sumCorr);
		    if ($prediction > MAX_RATING) $prediction=MAX_RATING;

		    if ($PCM->email == "rigaux@lri.fr")
		      {
			// echo "Paper $paper->id Sign.=$sumSignificance nbRatings=$nbRatings Prediction=$prediction<br>";
			SQLRating ($PCM->email, $paper->id, 
			       (int) ($prediction + 0.499), 
			       $sumSignificance/$nbRatings, $db);
		      }
		  }
	      }
	  }
      }
  }

  // Compute the correlation between two users

  function ComputeCorrelation ($email1, $email2, $db)
  {
   $sumNumerator = 0.;
   $sumDenom1 = 0.;
   $sumDenom2 = 0.;

   $avg1 = AvgUserRating ($email1, $db);
   $avg2 = AvgUserRating ($email2, $db);

   // Loop on papers
   $iCoRated=0;
   $qPaper = "SELECT * FROM Paper";
   $rPaper = $db->execRequete ($qPaper);
   while ($paper = $db->objetSuivant ($rPaper))
   {
     $rating1 = GetRating ($paper->id, $email1, $db); 
     $rating2 = GetRating ($paper->id, $email2, $db); 
     if (is_object($rating1) 
	 and is_object($rating2)
	 and $rating1->significance==1 
	 and $rating2->significance==1)
     {

       $sumNumerator += ($rating1->rate - $avg1) 
                          * ($rating2->rate - $avg2);
       $sumDenom1 +=  pow($rating1->rate - $avg1,2);
       $sumDenom2 +=  pow($rating2->rate - $avg2,2);

       $iCoRated++;
     }
   }

   $sumDenominator = sqrt ($sumDenom1 * $sumDenom2);

   if ($sumDenominator != 0) 
     {
       SQLCorrelation($email1, $email2, 
		      abs($sumNumerator)/$sumDenominator,
		      $iCoRated, $db);
     }
  }

?>