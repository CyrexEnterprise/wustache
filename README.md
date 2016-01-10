# Wustache
The Wustache implements the Mustache logicless templating in Wordpress. End then some.

#### Installation
Run ``composer install`` inside the plugin folder, and all dependencies will be gathered for you.
If you don't have composer installed, read [this article](http://blog.cloudoki.com/set-up-your-local-battleground/).

#### Mustache
Mustache is added to the composer file. Run ``composer install`` (and update aftwards) to maintain the latest version.

## Admin Tools
When activated, Wustache displays a discrete right-column Template box. 
You can select your desired template right from your theme's ``templates`` folder.

Additionally, all images connected to your post will be displayed. Drag for ordering, and manipulate the list both directly as through the classic Wordpress Media Manager.

## Publication
Once Wustache is activated, you can add simple parsing through the action ``the_template``.
Additionally, for deeper integration, you can use the **Wustache Classes** for theme level php inclusions.

*Inspect the demo files for more information*

# Feature Requests
-	Direct drag-n-drop image addition in admin Template box