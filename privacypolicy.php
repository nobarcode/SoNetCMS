<?php

include("class_config_reader.php");

//read config file
$config = new ConfigReader();
$config->loadConfigFile('assets/core/config/config.properties');

if ($config->readValue('displaySignUpAgreementLink') != 'true') {exit;}

print <<< EOF
<html>
<head>
<title>Terms and Conditions</title>
</head>

<body>
<h1>PRIVACY POLICY</h1>
<br>
<h2>INTRODUCTION</h2>
Your privacy is essential to us. Here at [OUR_WEBSITE], we believe that privacy is a top priority. We know that you care how information about you is used and shared. Thus, we provide this Privacy Policy to summarize our procedures and practices as regards to information collection and use. This will serve you as a guide in making an intelligent decision in sharing your information with us. By visiting [OUR_WEBSITE], you agree to be bound by this Privacy Policy and hereby accept the procedures and practices stated in this herein.
<br>
<br>
<br>
<h2>SCOPE</h2>
This Privacy Policy applies only within this website and other pages where this policy appears. This would describe and explain how we take care and handle your personal information you shared to us. By accepting the Privacy Policy, you expressly consent to our collection, storage, use and disclosure of your personal information as described in this Privacy Policy.
<br>
<br>
<br>
<h2>COLLECTION &amp; USAGE</h2>
If you attempt to use the services and applications of our website and or choose to provide information to us, this website shall collect Personal Information from you. This information includes, but is not limited to: name, address, telephone number, mobile number and/or email address. Once collected, we will store your information for a reasonable period of time for record keeping purposes. The information that we store is sometimes deleted as space requires or in the normal course of business.
<br>
<br>
<br>
<h2>DISCLOSURE</h2>
We may share information with governmental agencies or other companies assisting us in fraud prevention or investigation. We may do so when: (1) permitted or required by law; or, (2) trying to protect against or prevent actual or potential fraud or unauthorized transactions; or, (3) investigating fraud which has already taken place. The information is not provided to these companies for marketing purposes.
<br>
<br>
<br>
<h2>COOKIES</h2>
The Site may use cookie and tracking technology depending on the features offered. Cookie and tracking technology are useful for gathering information such as browser type and operating system, tracking the number of visitors to the Site, and understanding how visitors use the Site. Cookies can also help customize the Site for visitors.
<br>
<br>
<br>
<h2>SECURITY</h2>
All collected information is stored in a technically and physically secure environment. While we use SSL encryption to protect Sensitive Information online, we also do everything in our power to protect PII (including Sensitive Information) off-line. Unfortunately, no data transmission over the Internet can be guaranteed to be 100% secure. As a result, while we strive to protect our end-users' personal information, we cannot ensure or warrant the security of any information that you transmit to us, and you do so at your own risk.
<br>
<br>
<br>
<h2>ACCESSING AND UPDATING PERSONAL INFORMATION</h2>
When you use our services, we make good faith efforts to provide you with access to your personal information and either to correct this data if it is inaccurate or to delete such data at your request if it is not otherwise required to be retained by law or for legitimate business purposes. We ask individual users to identify themselves and the information requested to be accessed, corrected or removed before processing such requests, and we may decline to process requests that are unreasonably repetitive or systematic, require disproportionate technical effort, jeopardize the privacy of others, or would be extremely impractical, or for which access is not otherwise required. In any case where we provide information access and correction, we perform this service free of charge, except if doing so would require a disproportionate effort. Some of our services have different procedures to access, correct or delete users' personal information. We do retain personal information from closed accounts to comply with law, prevent fraud, collect any fees owed, resolve disputes, troubleshoot problems, assist with any investigations, enforce our policies and take other actions otherwise permitted by law.
<br>
<br>
<br>
<h2>THIRD PARTIES</h2>
We provide links to Web sites outside of our web sites, as well as to third party Web sites. These linked sites are not under our control, and we cannot accept responsibility for the conduct of companies linked to our website. Before disclosing your personal information on any other website, we advise you to examine the terms and conditions of using that Web site and its privacy statement.
<br>
<br>
<br>
<h2>MINORS</h2>
This Website is not intended for visitors that are minors or under eighteen (18) years of age. [OUR_WEBSITE] does not knowingly solicit or collect information from individuals under the age of eighteen (18). If we determine that we have inadvertently collected personal information from someone under eighteen (18) years of age, we will take reasonable steps to purge this information from our database. We encourage parents and guardians to spend time online with their children and to participate and monitor the Internet activities of their children.
<br>
<br>
<br>
<h2>CHANGES AND AMENDMENTS & NOTIFICATION OF CHANGES</h2>
[OUR_WEBSITE] reserves the right to change or update this Privacy Policy at any time by posting a clear and conspicuous notice on the Website explaining that we are changing our Privacy Policy. All Privacy Policy changes will take effect immediately upon their posting on the Website. Please check the Website periodically for any changes. Your continued use of the Website and/or acceptance of our e-mail communications following the posting of changes to this Privacy Policy will constitute your acceptance of any and all changes.
<br>
<br>
<br>
</body>
</html>
EOF;

?>