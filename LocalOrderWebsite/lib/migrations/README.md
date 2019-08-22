Better PHP Migrations
=====================

I've taken some parts of other migrations frameworks, and put together a very simple, small, versatile, and easy to use framework which runs on PHP and MySQL.


How to use:


You can choose to navigate to the parent directory in your browser, and run the migrations from the web interface, or you can just run them from the command line with:
   php index.php run
   
 
To create new migrations:

	-put just the .sql file within the /migrations/migrations/ folder, with the format:
	
		{index}_anyname.sql
		
	-the index should be numeric and in order so the system can maintain its dependencies.


You can configure your specific database settings in /migrations/lib/migrations.php, at the top of the file.

Right now the framework just keeps track of which .sql files are the in the /migrations folder. When you run migrations, it it keeps track of which have been executed by putting their filenames in the migrations table. Very simple.

There is no 'back' or undo feature as of yet. More features to come.
