#
# Create the database. If you change any value here, report
# the changes in the DBInfo.php file.
#

CREATE DATABASE Review;

#
# Create a MySQL user. Change 'localhost' to the name of the server
# that hosts MySQL.
#

GRANT ALL PRIVILEGES ON Review.* TO adminReview@localhost
       IDENTIFIED BY 'mdpAdmin';

#
# Create a MySQL user with restricted right for SQL queries
#

GRANT select ON Review.*  TO SQLUser@localhost IDENTIFIED BY 'pwdSQL';
