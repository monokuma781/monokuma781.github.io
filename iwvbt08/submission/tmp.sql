 
 
 INSERT INTO Config Values ('MyReview''06' /* conf acronym */,
 			'MyReview conf. management system',
                         'http://myreview.lri.fr/demo/',
 			   'myreview@lri.fr' /* Conf mail */,
 			   'myreview@lri.fr' /* Chair */,
                            'Eur' /* Currency */,
                            'myreview@lri.fr' /* Paypal account */,
 	                   'Y', /* Two phases submission */
 	                   'N', /* Blind review */
 	                   'Y', /* Extended submission form */
 	                   'Y', /* Multi-topics */
                             NOW() /* Submission deadline */,
                             NOW() /* Review deadline */,
 		            NOW() /* CR deadline */,
                             'Y' /* Abstract submission is open */,
                             'Y' /* Submission is open */,
                             1 /* Discussion is closed by default */,
                             2 /* Ballot mode = general by default */,
 			    'N' /* Camera Ready phase is not yet open */,
  			   'pwd' /* Simple password generator */,
  			   'FILES' /* Default dir. for uploaded files */,
  			   'pdf' /* Default: accept only PDF files */,
                            '3' /* RevPerPaper*/,
                       '999',  /* Size of ballots */    
                           'Y', 'Y', 'N',
 			 '0' /* Status of selected papers*/, 
 			'0'  /* Position wrt the rate below*/, 
 			'0'  /* rate of reference  */,
                         'All' /* All reviewers */,
                         'A' /* With or without conflict */,
                         'A' /* With or without missing review */,
                         0 /* Any topic */,
                         'Any' /* Any title */,
                         'Any' /* Any author */,
                         'A' /* Uploaded or not */,
                         '' /* Encoding of paper questions /answers */,
                         '' /* Encoding of review questions /answers */,
 	                0, 0 /* No selected topic */,
                        'Manual install', NOW(),
 		      'N' /* Do not use mega upload */
 	);

