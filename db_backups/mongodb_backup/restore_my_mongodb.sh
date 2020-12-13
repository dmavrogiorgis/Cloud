#! /bin/bash

mongoimport --host mongo_db --db cloud_mongo_db --collection Cinemas --type json --file /mongo_restore/my_mongodb/Cinemas --jsonArray
mongoimport --host mongo_db --db cloud_mongo_db --collection Favourites --type json --file /mongo_restore/my_mongodb/Favourites --jsonArray
mongoimport --host mongo_db --db cloud_mongo_db --collection Movies --type json --file /mongo_restore/my_mongodb/Movies --jsonArray
mongoimport --host mongo_db --db cloud_mongo_db --collection Notifications --type json --file /mongo_restore/my_mongodb/Notifications --jsonArray
mongoimport --host mongo_db --db cloud_mongo_db --collection Subscriptions --type json --file /mongo_restore/my_mongodb/Subscriptions --jsonArray

mongoimport --host mongo_db_orion --db orion --collection entities --type json --file /mongo_restore/orion_mongodb/entities --jsonArray
mongoimport --host mongo_db_orion --db orion --collection csubs --type json --file /mongo_restore/orion_mongodb/csubs --jsonArray