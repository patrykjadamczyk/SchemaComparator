# Schema Comparator
Schema comparator looks for schema differences comparing 4 .csv files - before and after the update.
By default it opens files from paths: ```pre/columns.csv```, ```pre/constraints.csv```, ```post/columns.csv``` and ```post/constraints.csv```
The class is capable of detecting:
* Columns that are present in ```pre/*.csv``` file but don't exist in ```post/*.csv``` file
* column type difference
* default column value difference
* auto_increment difference
* column's nullable property difference

Differences in columns are saved to ```log/columns.log```, differences in constraints are saved to ```log/constraints.log```
by default.

# How to use
1. Paste .csv files to ```pre/``` and ```post/``` folders
2. If files don't match the default path, set the path in the constructor in ```index.php``` like so: ```$schemaComparator = new SchemaComparator('pre/column_file.csv', 'post/column_file.csv', 'pre/constraint_file.csv', 'post/constraint_file.csv')```
4. Execute by calling the following terminal command: ```php index.php```
5. Read the output in the ```log/``` directory
