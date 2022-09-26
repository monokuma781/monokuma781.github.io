mysqldump -u adminReview -h localhost  -p -t Review Paper Review PCMember > SvDB
