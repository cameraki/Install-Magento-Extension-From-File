# Install-Magento-Extension-From-File
Simply copies all files from a Magento extension into the relevant folders in your Magento install. 

What does this solve?
Having to unzip, copy each file and folder and paste them in your relevant Magento folders one by one.

Just run this file in command line as follows:

`php install-magento-extension.php /path/to/extension.zip /path/to/magento/root`

Note: `/path/to/magento/root` is not required. If no path is entered it defaults to the current directory.

# Actions
`--help` Use this to show the help page.

`--nozip` Use this if the extension you are providing is not in a ZIP file, instead its a folder.

`--straight` Use this if the ZIP file you are supplying contains the direct content of the extension and not the traditional folder with the extension name then all the contents inside. E.g 1) ZIP > ExtensionName > app,skin ... vs 2) ZIP > app,skin ... Use `--straight` if the extension is like 2) 

Example usage of actions
```
php install-magento-extension.php /path/to/extension /path/to/magento/root --nozip
php install-magento-extension.php /path/to/extension.zip /path/to/magento/root --straight
php install-magento-extension.php --help
```
