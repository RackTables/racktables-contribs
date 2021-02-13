# README #

## Configuration of graphTopo plugin ##

Follow these steps:

#### System libraries

```bash
sudo apt install graphviz 
sudo apt install python3-mysqldb
sudo apt install libgraphviz-dev 

sudo pip3 install -r requirements.txt
```
#### Compile the python code

```python
python3 -m nuitka topoGen.py --nofollow-import-to=MySQLdb --nofollow-import-to=graphviz --nofollow-import-to=pydot --nofollow-import-to=time --nofollow-import-to=sys --nofollow-import-to=functools --nofollow-import-to=datetime --nofollow-import-to=pandas --nofollow-import-to=networkx --nofollow-import-to=operator --nofollow-import-to=itertools --nofollow-import-to=re --nofollow-import-to=matplotlib --nofollow-import-to=pyvis --follow-imports
```

#### Copy Files
This includes the binary generated in the second step

```bash
sudo cp graph.php /var/www/racktables/plugins/
sudo cp topoGen.bin /var/www/racktables/plugins/
sudo cp settings.yml /var/www/racktables/plugins/

sudo cp logo.png /var/www/racktables/wwwroot/pix/
sudo cp not.gif /var/www/racktables/wwwroot/pix/

sudo cp js/ /var/www/racktables/plugins/ -r
sudo cp css/ /var/www/racktables/plugins/ -r
```

#### Link 
This is needed so the `graph.php` file can access the `topoGen.bin`.
```bash
cd /var/www/racktables/wwwroot/
sudo sudo ln ../plugins/ . -s
```

#### Set permissions
This is needed for topologies storage
```bash
cd /var/www/racktables/wwwroot/
sudo chown www-data:www-data pix/ -R
sudo chown www-data:www-data plugins/
```

#### SQL ReadOnly User

```mysql
create user 'viewRT'@'%' identified by 'viewRT';
grant select,lock tables,show view on racktables.* to 'viewRT'@'%';
```

#### Edit settings.yml
Edit DB name as needed

## TODO

1. Implement the new RackTables plugin-architecture in the file `graph.php`.
2. Get rid of `graphviz` and try to manage all the topologies natively through `networkx`. This will make the code a lot cleaner.
3. Use OpenStreetMap to visualize topologies on a Map.