Tako assistant
====

## Overview

Google Home App 'Tako Assistant' scripts.

## Description

Tako Assistant is an app which is operated using the Google Home smart speaker.
This document details the source code and installation information necessary to operate this app.
Please note that while this app is built to support certain services and servers, these will not be explained in this manual.

**Tako Assistant Basic Function**

This app finds and ranks the best places to fly a kite from a previously registered list of multiple kite-flying locations based on the following 3 criteria:

- Preference index of kite-flying location
- Distance to kite-flying location from base location (residence)
- Type of kite most suited to wind speed


**System Diagram and Process Outline**

![image_components](https://github.com/mashstudio/takoassistant/blob/image/images/image_components.png)

- The Server accesses darksky.net every hour to obtain the wind speed and wind direction for multiple kite-flying locations that are registered in the database, then stores them in the database.
- As defined by Action on Google, the Tako Assistant app analyzes the language from the voice input received through Google Home using Dialogflow, and requests the Server for the necessary information contained in the utterance through Firebase.
- The Server receives the necessary information from the database according to the request, then returns the analyzed results to Firebase.
- Firebase, having received the necessary information in the utterance from the Server, produces the speech for Google Home.


**Roles of Services and Components**

- **Action on Google**  
Handles the definitions and operation verification through simulators for the Tako Assistant app.

- **Dialogflow**  
Handles the definitions and language analysis for the flow of the dialog.

- **Firebase (Fulfillment)**  
Requests the Server for language analysis results from Dialogflow and other necessary information, then structures the speech.  
Please note that in scenarios where Firebase requests data from external servers, including this scenario, a paid Firebase subscription plan is required.

- **Server (WebServer)**  
Manages kite-flying spots, collects weather information (wind information), and returns kite-flying spot results based on the request from Firebase.

- **DarkSky.net**  
Weather information service (https://darksky.net)  
Includes an API allowing weather information to be obtained from coordinates (latitude and longitude).  
Up to 1,000 requests per day can be made free of charge, including for commercial purposes.

## Requirement

- Action on Google account
- Paid Firebase plan
- DarkSky API (weather information retrieval) account
- Web server (compatible with PHP, PostgreSQL, and Cron)


## Install

**1. Database (PostgreSQL) Setup**  
This document assumes that the Server is already configured to use PostgreSQL through PHP.

![image_components_database](https://github.com/mashstudio/takoassistant/blob/image/images/image_components_database.png)

The Database stores information on kite-flying locations (location_table) and weather information corresponding to these locations (weather_table).  
Using a database management tool (such as phpPgadmin), create a database with a name of your choice, and create tables and records as described below.

Relevant SQL code is contained in the source code.

SQL command for creating a table  
`sql/PostgreSQL/create_tables.sql`

Sample data for location information (location_table)  
`sql/PostgreSQL/insert_location.sql`

**Table contents**

location_table  
![image_location_table](https://github.com/mashstudio/takoassistant/blob/image/images/image_location_table.png)

- pkey  
Primary key for table

- name  
Location name as to be articulated by Google Home

- keyword  
Keywords used to search for locations, separated by commas

- fullname  
Official location name

- latitude,longitude  
Latitude and longitude of location

- rating  
Rating based on preference  
Value between 0.0 and 1.0

- delflag  
Flag for deletion  
1 = deleted


weather_table  
![image_weather_table](https://github.com/mashstudio/takoassistant/blob/image/images/image_weather_table.png)

- pkey  
Primary key for table

- location_table_pkey  
pkey for referencing location_table

- date , time  
Date and time wind speed information was retrieved

- wind  
Wind speed

- wind_direction_angle  
Wind direction: 0 to 359 degrees, 0 = north, clockwise

- wind_direction_jp  
Wind direction to be articulated by Google Home


---
**2. Server Script Installation**  
**2-1. PHP Setup**

![image_component_php](https://github.com/mashstudio/takoassistant/blob/image/images/image_component_php.png)

The script to be installed on the Server is in the `src/php/takoassistant` directory.

Gets weather information from DarkSky.net and stores it in the database  
`src/php/takoassistant/load_weather.php`  

API which receives requests from Firebase  
`src/php/takoassistant/search.php`  

Library to be used by PostgreSQL  
`src/php/takoassistant/lib/simple_pg.php`    

General configuration  
`src/php/takoassistant/incs/config.inc.php`  

Kite Configuration  
`src/php/takoassistant/incs/constant.php`  


Open `/takoassistant/incs/config.inc.php` in an editor and run the following configurations:

```
define("DB_HOST", "<DB_ADDRESS>");  
define("DB_PORT", "<DB_PORT>");  
define("DB_USER_NAME", "<DB_USER_NAME>");  
define("DB_PWD", "<DB_PASSWORD>");  
define(“DB_NAME”,”<DB_NAME>”);  
```
Change the values of **<DB_ADDRESS> <DB_PORT> <DB_USER_NAME> <DB_PASSWORD> <DB_NAME>** according to the settings of the database being used.

```
define("HOME_LATITUDE","34.822602");  
define("HOME_LONGITUDE","137.396672");  
```
Set the values of **HOME_LATITUDE** and **HOME_LONGITUDE** to the base location coordinates (latitude and longitude).  
The initial values are set for Toyokawa Station, Japan.  
This app ignores the smart speaker installation coordinates data, and uses fixed values.

```
define("DARKSKY_API_URL","https://api.darksky.net/forecast/<DARKSKY_API_KEY>/");
define("DARKSKY_API_URL_OPTION","?lang=ja&units=si")
```

Set **<DARKSKY_API_KEY>** to the key supplied by DarkSky.net for use of the API.  
**DARKSKY_API_URL_OPTION** defines the language and units for retrieving information from DarkSky.net  
For more details, please refer to the DarkSky.net API usage documentation (https://darksky.net/dev/docs).


**2-2. Installing Script on Server**

Using an FTP client, copy the "takoassistant" directory in src/php to a directory under "htdocs" (or "www" etc.) in the Web Server.


**2-3. Confirming Script Operation on Server**

When the location information is entered in location_table on the database, it will be accessible at http://<YOUR_SERVER_URL>/takoassistant/load_weather.php via a web browser.  
Replace **<YOUR_SERVER_URL>** with the URL of the Server being used.  
The time to perform the operation depends on the number of locations stored (20 locations should take around 30 seconds). Once it is finished, verify the contents of weather_table on the database. If wind data has been stored in weather_table, access it using the following API in a web browser:

http://**<YOUR_SERVER_URL>**/takoassistant/search.php?mode=score

If information is displayed for all registered locations, setup is complete.

---
**3. Action on Google Setup**

![image_components_aog](https://github.com/mashstudio/takoassistant/blob/image/images/image_components_aog.png)

Log in to Action on Google.

![image_aog_add](https://github.com/mashstudio/takoassistant/blob/image/images/image_aog_add.png)


Click on "Add/import project"


![image_aog_new_project](https://github.com/mashstudio/takoassistant/blob/image/images/image_aog_new_project.png)

- Enter "Kite" in the "Project Name" field
- Select the language you would like to use for "Choose the default language for your Actions"
- Select your region for "Choose your country or region"

Please note that this app was originally developed in Japanese. Please adjust settings for the language you wish to build the app in. This manual will use English in its examples.

![image_aog_add_finish](https://github.com/mashstudio/takoassistant/blob/image/images/image_aog_add_finish.png)

Select "SKIP" on the screen shown above.

![image_aog_overview](https://github.com/mashstudio/takoassistant/blob/image/images/image_aog_overview.png)


Select "Decide how your Action is invoked" under "Quick setup"

![image_aog_invocation](https://github.com/mashstudio/takoassistant/blob/image/images/image_aog_invocation.png)

Enter "Tako Assistant" in the "Display name" field  
Choose your preferred option for the Google Assistant Voice. In this example, "Female 1" is selected.  
Finally, click on the "SAVE" button to save the settings.

![image_aog_overview2](https://github.com/mashstudio/takoassistant/blob/image/images/image_aog_overview2.png)

Select "Add Action(s)" under "Build your Action"


![image_aog_add_actions](https://github.com/mashstudio/takoassistant/blob/image/images/image_aog_add_actions.png)

Select "ADD YOUR FIRST ACTION"

![image_aog_custom_intent](https://github.com/mashstudio/takoassistant/blob/image/images/image_aog_custom_intent.png)

Select "Custom intent," then "BUILD"

---
**4. Dialogflow Setup**

![image_components_dialogflow](https://github.com/mashstudio/takoassistant/blob/image/images/image_components_dialogflow.png)

**4-1-1.  Dialogflow Language and Time Zone Configuration**

![image_dialogflow_create](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_create.png)

Select the language and time zone you would like to use under "DEFAULT LANGUAGE" and "DEFAULT TIME ZONE," then click on "CREATE."  
The app was originally developed using "Japanese – ja" and "(GMT+9:00) Asia/Tokyo" for Japanese/Japan time.


**4-1-2. Dialogflow Configuration**

![image_dialogflow_settings](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_settings.png)

Click on the Settings icon (the cogwheel), select "V2 API" under "API VERSION," then click on "SAVE."


**4-2. Entities Setup**

Here, lists will be set up in order to convert the vocabulary (natural language) used by the app into set terms (entities).

![image_dialogflow_entity](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_entity.png)

Select "Entities," then select "CREATE ENTITY."

![image_dialogflow_entity_kitetype](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_entity_kitetype.png)

Set types of kites and corresponding words.

Enter "KiteType" in the "Entity name" field.  
In this example, there are 4 kinds of kites, which have been entered as shown in the screenshot above.


**4-3. Intents Setup**

Intents need to be created in order to process natural language.  
The kite app is designed such that the conversation will follow the flow chart shown below.

![image_dialogflow_appflow_en](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_appflow_en.png)  
![image_dialogflow_appflow_jp](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_appflow_jp.png)


**4-3-1. Default Welcome Intents Setup**

![image_dialogflow_intent_select_default](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_select_default.png)

Select "Intents," then "Default Welcome Intent"

![image_dialogflow_appflow_01](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_appflow_01.png)

The actions for when the Tako Assistant app is started are configured here.  
Configure the actions as shown in the screenshots below.

![image_dialogflow_intent_default_welcome_01](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_default_welcome_01.png)  
![image_dialogflow_intent_default_welcome_02](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_default_welcome_02.png)  
![image_dialogflow_intent_default_welcome_03](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_default_welcome_03.png)  
![image_dialogflow_intent_default_welcome_04](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_default_welcome_04.png)


**4-3-2. Adding and Setup of "quit" Intent**
 
![image_dialogflow_create_quit_intent](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_create_quit_intent.png)

The actions for quitting the Tako Assistant app are defined here.  
Configure the actions as shown in the screenshots below.

![image_dialogflow_intent_quit_01](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_quit_01.png)  
![image_dialogflow_intent_quit_02](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_quit_02.png)  
![image_dialogflow_intent_quit_03](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_quit_03.png)  
![image_dialogflow_intent_quit_04](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_quit_04.png)  
![image_dialogflow_intent_quit_05](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_quit_05.png)


**4-3-3. Adding and Setup of "I would like to go to" Intent**

![image_dialogflow_appflow_i_would_like_to_go_to](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_appflow_i_would_like_to_go_to.png)

The language analysis for the section in the Tako Assistant app shown above is configured here.  
Add this intent then configure it as shown in the screenshots below.

![image_dialogflow_intent_i_would_like_to_go_to_01](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_i_would_like_to_go_to_01.png)  
![image_dialogflow_intent_i_would_like_to_go_to_02](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_i_would_like_to_go_to_02.png)  
![image_dialogflow_intent_i_would_like_to_go_to_03](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_i_would_like_to_go_to_03.png)  
![image_dialogflow_intent_i_would_like_to_go_to_04](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_i_would_like_to_go_to_04.png)  
![image_dialogflow_intent_i_would_like_to_go_to_05](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_i_would_like_to_go_to_05.png)

When registering the "Training phrases," in order to assign the location ("Toyohashi Sports Park" in this example) to the relevant parameter, register the configuration as described below.

![image_dialogflow_intent_i_would_like_to_go_to_06](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_i_would_like_to_go_to_06.png)

Select the text "Toyohashi Sports Park" using the cursor to display a drop-down menu. In this menu, select "@sys.any".

![image_dialogflow_intent_i_would_like_to_go_to_07](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_i_would_like_to_go_to_07.png)


**4-3-4. Adding and Setup of"I would like a suggestion" Intent**

![image_dialogflow_appflow_i_would_like_a_suggestion](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_appflow_i_would_like_a_suggestion.png)

The language analysis for the section in the Tako Assistant app shown above is configured here.  
Add this intent then configure it as shown in the screenshots below.

![image_dialogflow_intent_i_would_like_a_suggestion_01](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_i_would_like_a_suggestion_01.png)  
![image_dialogflow_intent_i_would_like_a_suggestion_02](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_i_would_like_a_suggestion_02.png)  
![image_dialogflow_intent_i_would_like_a_suggestion_03](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_i_would_like_a_suggestion_03.png)  
![image_dialogflow_intent_i_would_like_a_suggestion_04](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_i_would_like_a_suggestion_04.png)  
![image_dialogflow_intent_i_would_like_a_suggestion_05](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_i_would_like_a_suggestion_05.png)  
![image_dialogflow_intent_i_would_like_a_suggestion_06](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_i_would_like_a_suggestion_06.png)


**4-3-5. Adding and Setup of "Do you know which kite you want to fly - NO" Intent**

![image_dialogflow_appflow_do_you_know_which_kite_you_want_to_fly_NO](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_appflow_do_you_know_which_kite_you_want_to_fly_NO.png)

The language analysis for the section in the Tako Assistant app shown above is configured here.  
Add this intent then configure it as shown in the screenshots below.

![image_dialogflow_intent_do_you_know_which_kite_you_want_to_fly_NO_01](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_do_you_know_which_kite_you_want_to_fly_NO_01.png)  
![image_dialogflow_intent_do_you_know_which_kite_you_want_to_fly_NO_02](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_do_you_know_which_kite_you_want_to_fly_NO_02.png)  
![image_dialogflow_intent_do_you_know_which_kite_you_want_to_fly_NO_03](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_do_you_know_which_kite_you_want_to_fly_NO_03.png)  
![image_dialogflow_intent_do_you_know_which_kite_you_want_to_fly_NO_04](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_do_you_know_which_kite_you_want_to_fly_NO_04.png)  
![image_dialogflow_intent_do_you_know_which_kite_you_want_to_fly_NO_05](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_do_you_know_which_kite_you_want_to_fly_NO_05.png)


**4-3-6. Adding and Setup of "Do you know which kite you want to fly - YES" Intent**

![image_dialogflow_appflow_do_you_know_which_kite_you_want_to_fly_YES](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_appflow_do_you_know_which_kite_you_want_to_fly_YES.png)

The language analysis for the section in the Tako Assistant app shown above is configured here.  
Add this intent then configure it as shown in the screenshots below.  
In this intent, the following step can be skipped if the user responds directly with a kite type instead of saying "Yes."

![image_dialogflow_intent_do_you_know_which_kite_you_want_to_fly_YES_01](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_do_you_know_which_kite_you_want_to_fly_YES_01.png)  
![image_dialogflow_intent_do_you_know_which_kite_you_want_to_fly_YES_02](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_do_you_know_which_kite_you_want_to_fly_YES_02.png)  
![image_dialogflow_intent_do_you_know_which_kite_you_want_to_fly_YES_03](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_do_you_know_which_kite_you_want_to_fly_YES_03.png)  
![image_dialogflow_intent_do_you_know_which_kite_you_want_to_fly_YES_04](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_do_you_know_which_kite_you_want_to_fly_YES_04.png)  
![image_dialogflow_intent_do_you_know_which_kite_you_want_to_fly_YES_05](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_do_you_know_which_kite_you_want_to_fly_YES_05.png)

When registering these training phrases, in order to assign the kite type ("the falcon kite" in this example) to the relevant parameter, select the text "the falcon kite," then select  "@KiteType" from the popup menu that appears.

![image_dialogflow_intent_do_you_know_which_kite_you_want_to_fly_YES_06](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_do_you_know_which_kite_you_want_to_fly_YES_06.png)


**4-3-7. Adding and Setup of "Which kite you want to fly" Intent**

![image_dialogflow_appflow_which_kite_you_want_to_fly](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_appflow_which_kite_you_want_to_fly.png)

The language analysis for the section in the Tako Assistant app shown above is configured here.  
Add this intent then configure it as shown in the screenshots below.

![image_dialogflow_intent_which_kite_you_want_to_fly_01](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_which_kite_you_want_to_fly_01.png)  
![image_dialogflow_intent_which_kite_you_want_to_fly_02](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_which_kite_you_want_to_fly_02.png)  
![image_dialogflow_intent_which_kite_you_want_to_fly_03](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_which_kite_you_want_to_fly_03.png)

When registering these training phrases, in order to assign the kite type ("the falcon kite" in this example) to the relevant parameter, select the text "the falcon kite," then select "@KiteType" from the popup menu that appears.

![image_dialogflow_intent_which_kite_you_want_to_fly_04](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_intent_which_kite_you_want_to_fly_04.png)


**4-4-1. Fulfillment Setup**

Select "Fulfillment" from the side menu.  
Configure the actions following the screenshots below.

![image_dialogflow_fulfillment](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_fulfillment.png)

Select the "index.js" tab, then copy and paste the Fulfillment Script at the end of this document into the inline editor.  
In line 11, change the <YOUR_SERVER_URL> value in the base_url variable to the Server URL where the API is installed.

Next, select the "package.json" tab, and add "request": "^2.88.0" as shown in the screenshot below.

![image_dialogflow_fulfillment_package_json](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_fulfillment_package_json.png)

Finally, make sure to click the "DEPLOY" button before you finish.


**4-4-2. Changing to a Paid Firebase Plan**

Requests from Firebase to an external server cannot be made using the free plan.  
It is necessary to change to a paid plan using the following steps.

![image_dialogflow_filfullment_console](https://github.com/mashstudio/takoassistant/blob/image/images/image_dialogflow_filfullment_console.png)

Select "View execution logs in the Firebase console" at the bottom of the Fulfillment page.

![image_firebase](https://github.com/mashstudio/takoassistant/blob/image/images/image_firebase.png)

Click on the plan name to the right of the project name.  
In the above screenshot, a paid plan has already been selected. If the free plan is selected, "Spark Plan" will be displayed.

![image_firebase_plans](https://github.com/mashstudio/takoassistant/blob/image/images/image_firebase_plans.png)

Select a paid plan.
You may choose either a fixed price or pay-as-you-go price plan. In the example shown in the screenshot above, the fixed price plan is selected.


**4-4-3. Fulfillment script**

Open the source code fulfillment/dialogflow/index.js in a text editor, the copy and paste the code into the inline editor on the Fulfillment page.  
Also copy and paste the code into package.json.

---
**5. Confirming Operation**

After basic setup has been completed, confirm that the app operates properly.  
Confirm using the Simulator.

![image_aog_simulator](https://github.com/mashstudio/takoassistant/blob/image/images/image_aog_simulator.png)



## License
MIT

## Author
Jun Masuda
