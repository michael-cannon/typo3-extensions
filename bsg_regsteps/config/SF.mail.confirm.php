<?php
$strData = <<<EOT
{$arrData['company']}
{$arrData['address']}
{$arrData['city']}, {$arrData['country']}, {$arrData['zip']}
 
Dear {$arrData['first_name']}:

This is your receipt and confirmation* for your registration to BrainStorm San Francisco to be held June 29-July 2, 2009.

Please bring a copy of this email and a business card on-site to our registration area to pickup your badge and Materials. 
        
Your agenda:
Conference series: {$conf_series}
{$courses}

{$proAccount}

Paid in full by cardholder: {$payData['userData']['cholder']}
Total amount paid: $ {$total} 
Priority code used: {$priorityCode}

{$login}

Host Hotel Information: We have reserved a limited block of rooms at the Parc 55 Hotel at 55 Cyril Magnin Street,San Francisco, CA 94102. Please call 1-800-697-3103 between 7:00am-6:00pm, Monday through Friday, Pacific Standard Time to make reservations.  Attendees should reference BrainStorm Group in order to receive the Group Rate of $249.  The Group Rate is available until June 15, 2009 and is based upon availability.  Early booking is strongly advised. 
 
Important Information: 
Registration Opens at 7:00 AM each day.  Please arrive early to gather your materials.
 
Attire: Business casual, comfortable attire is recommended. Please bring a light jacket or sweater, as meeting room temperatures may vary.
 
Cancellation Policy: We will accept substitute registrants at any time or gladly transfer your registration to another event. No refunds are given.

If you have any questions, please call me at 508-475-0475 ext.15 between 9am and 5pm eastern.

Thank you for your registration. We look forward to hosting you at BrainStorm San Francisco!
 
Sincerely,
Jane E. Waring~Pelkey
Client Services
508-475-0475, x15
registrar@brainstorm-group.com
www.BPMInstitute.org
www.SOAInstitute.org
EOT;

?>