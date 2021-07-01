INSERT INTO `help` VALUES ('import-overleaf','Import project from Overleaf','Her
e you can import your projects directly from Overleaf.\r\n<p>\r\nFor details, pl
ease read <a href=\"https://www.overleaf.com/learn/how-to/Using_Git_and_GitHub\"
 target=\"_blank\">Using Git and GitHub with Overleaf</a>.\r\n</p>\r\n<p>\r\nLat
er you can also update (<em>git pull</em>)  your projects on the <a href=\"retva
l_abc.php\">Documents alphabetically</a> page. Just click on the green Overleaf 
icon <img src=\"/css/img/overleaf16.svg\"> and your project will be updated. \r\
n</p>\r\n<p>\r\nThe access is only readonly. Texmlbus will not change any data o
n your Overleaf project.\r\n');

INSERT INTO `help` VALUES ('import-overleaf-name','Specify the name of the proje
ct','Please enter the name of your project.\r\n<p>\r\nYour project has probably 
a name on Overleaf. Just use that name or any other name. Within texmlbus, your 
project will be given the name that you enter here.\r\n\r\n');

INSERT INTO `help` VALUES ('import-overleaf-projectid','Specify projectid','Plea
se specify the project id of your project.\r\nYou can find the Git url from the 
project url (the url in the browser address bar when you are in a project). If y
our Overleaf project url looks like:\r\n<p>\r\n<tt>https://www.overleaf.com/proj
ect/1234567</tt>\r\n</p>\r\nyou will need to enter <tt>1234567</tt> here.\r\n<p>
\r\nFor details, please check the <a href=\"https://www.overleaf.com/learn/how-t
o/Using_Git_and_GitHub\" target=\"_new\">Git help page on Overleaf</a>.\r\n</p>'
);

INSERT INTO `help` VALUES ('import-overleaf-select','Select set for import','Ple
ase select a set where your project should be imported to. You can also just cre
ate a new set by just typing the new name, followed by the enter / return key.\r
\n<br />\r\nIf you do not specify a set, the set <em>main</em> is automatically 
chosen. \r\n<p>\r\nA set is basically just a subdirectory in the <em>article</em
> folder. ');

INSERT INTO `help` VALUES ('import-overleaf-username','Your username','Please sp
ecify your username on Overleaf.\r\n<p>\r\nYou will be then asked for a password
. The password will only be used to access Overleaf and <b>never</b> be sent any
where else.\r\n</p>\r\n<p> \r\nThe password will be cached in shared memory of t
he container until the docker container is stopped. It will <b>never</b> be save
d to disk or stored persistently in any other way.\r\n</p>\r\n');

