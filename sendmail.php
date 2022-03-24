<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';


// ini_set('display_errors', 1); 
// error_reporting(E_ALL);

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

//Tell PHPMailer to use SMTP
$mail->isSMTP();



$data = array_merge($_POST, $_FILES);
var_dump($data);

//Enable SMTP debugging
//SMTP::DEBUG_OFF = off (for production use)
//SMTP::DEBUG_CLIENT = client messages
//SMTP::DEBUG_SERVER = client and server messages
// $mail->SMTPDebug = SMTP::DEBUG_SERVER;

// $mail->SMTPDebug = 2;


//Set the hostname of the mail server
//use localhost if you are using GoDaddy's hosting service
$mail->Host = 'localhost';

//Use `$mail->Host = gethostbyname('smtp.gmail.com');`
//if your network does not support SMTP over IPv6,
//though this may cause issues with TLS

//Set the SMTP port number:
// - 465 for SMTP with implicit TLS, a.k.a. RFC8314 SMTPS or
// - 587 for SMTP+STARTTLS
$mail->Port = 25; //use port 25 if you are using GoDaddy's hosting service
$mail->SMTPAutoTLS = false; 

//Set the encryption mechanism to use:
// - SMTPS (implicit TLS on port 465) or
// - STARTTLS (explicit TLS on port 587)
// $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

//Whether to use SMTP authentication
$mail->SMTPAuth = false;

//Username to use for SMTP authentication - use full email address for gmail, change the value with your own email address
$mail->Username = 'test1@test.com';

//Password to use for SMTP authentication, change the value with your own credential
$mail->Password = 'test!';

//Set who the message is to be sent from
//Note that with gmail you can only use your account address (same as `Username`)
//or predefined aliases that you have configured within your account.
//Do not use user-submitted addresses in here
$mail->setFrom('test1@test.com', 'Name1');

//Set an alternative reply-to address
//This is a good place to put user-submitted addresses
$mail->addReplyTo('test1@test.com', 'Name1');

//Set who the message is to be sent to
$mail->addAddress('test2@test.com', 'Name2');

//Set the subject line
$mail->Subject = 'Subject: Title';

//Construct the form details based on the user's input

$message = "Form Details:<br>";
$message .= "Loan Type: " . $data['loanType'] . "<br>";

if($data['applyForSomeone'] == 'Yes') {
    $message .= "Apply for Someone?: ". $data['applyForSomeone'] ."<br>";
    $message .= "Full Name: " . $data['AFS_fullName'] . "<br>";
    $message .= "Company Name: ". $data['AFS_companyName'] . "<br>";
    $message .= "Email: " . $data['AFS_email'] . "<br>";
    $message .= "Contact: " . $data['AFS_contact'] . "<br>";
}else{
    $message .= "Apply for Someone?: " . $data['applyForSomeone'] . "<br>";
}

$message .= "Loan Amount: $" . number_format($data['loanAmount'][$i], 2, '.', ',') . "<br>";
$message .= "Term Length: " . $data['termLength'] . "<br>";
$message .= "First Name: " . $data['firstName'] . "<br>";
$message .= "Last Name: " . $data['lastName'] . "<br>";
$message .= "Email Address: " . $data['emailAddress'] . "<br>";
$message .= "Phone Number: " . $data['phone Number'] . "<br>";
$message .= "Address: " . $data['address'] . "<br>";
$message .= "Comment: " . $data['comment'] . "<br>";
$message .= "Company Name: " . $data['s3_companyName'] . "<br>";
$message .= "ACN: " . $data['s3_acn'] . "<br>";
$message .= "Assets: <br>";
for ($i=0; $i < count($data['assetDescription']) ; $i++) { 
    $message .= $data['assetDescription'][$i] . ": $" . number_format($data['assetValue'][$i], 2, '.', ',') . "<br>";
}
$message .= "Liabilities: <br>";
for ($i=0; $i < count($data['liability_desc']) ; $i++) { 
    $message .= $data['liability_desc'][$i] . ": $" . number_format($data['liability_value'][$i], 2, '.', ',') . "<br>";
}

$mail->msgHTML($message);

//Replace the plain text body with one created manually
$mail->AltBody = 'This is a plain-text message body';

//Attach an image file
// $mail->addAttachment('images/phpmailer_mini.png');

for ($i=0; $i < count($data['documents_liability']['name']); $i++) { 
    $file_tmp  = $data['documents_liability']['tmp_name'][$i];
    $file_name = $data['documents_liability']['name'][$i];

    $mail->addAttachment($file_tmp, $file_name);
}

for ($i=0; $i < count($data['documents_identification']['name']); $i++) { 
    $file_tmp  = $data['documents_identification']['tmp_name'][$i];
    $file_name = $data['documents_identification']['name'][$i];

    $mail->addAttachment($file_tmp, $file_name);
}

//send the message, check for errors
if (!$mail->send()) {
    $response = array('status' => 'error');
} else {
    $response = array('status' => 'success', 'data' => $data);
    //Section 2: IMAP
    //Uncomment these to save your message in the 'Sent Mail' folder.
    #if (save_mail($mail)) {
    #    echo "Message saved!";
    #}
}
// var_dump($data);
// var_dump($response);

echo json_encode($response);   

//Section 2: IMAP
//IMAP commands requires the PHP IMAP Extension, found at: https://php.net/manual/en/imap.setup.php
//Function to call which uses the PHP imap_*() functions to save messages: https://php.net/manual/en/book.imap.php
//You can use imap_getmailboxes($imapStream, '/imap/ssl', '*' ) to get a list of available folders or labels, this can
//be useful if you are trying to get this working on a non-Gmail IMAP server.
function save_mail($mail)
{
    //You can change 'Sent Mail' to any other folder or tag
    $path = '{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail';

    //Tell your server to open an IMAP connection using the same username and password as you used for SMTP
    $imapStream = imap_open($path, $mail->Username, $mail->Password);

    $result = imap_append($imapStream, $path, $mail->getSentMIMEMessage());
    imap_close($imapStream);

    return $result;
}
