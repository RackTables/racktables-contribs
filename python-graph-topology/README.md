# README #

## Configuration of graphTopo plugin ##

Follow these steps:

#### System libraries

```bash
sudo apt install graphviz 
sudo apt install python-mysqldb

sudo pip install pandas
sudo pip install graphviz
sudo pip install networkx
sudo pip install pydot
sudo pip install pyyed
sudo pip install matplotlib
sudo pip install nuitka
```

#### Compile the python code

```python
python -m nuitka draw_topo_26_estable.py --nofollow-import-to=MySQLdb --nofollow-import-to=graphviz --nofollow-import-to=pydot --nofollow-import-to=time --nofollow-import-to=sys --nofollow-import-to=functools --nofollow-import-to=datetime --nofollow-import-to=pandas --nofollow-import-to=networkx --nofollow-import-to=operator --nofollow-import-to=itertools --nofollow-import-to=re --nofollow-import-to=matplotlib --follow-imports
```

#### Copy Files
This includes the binary generated in the second step

```bash
sudo cp graph.php /var/www/racktables/plugins/
sudo cp draw_topo_26_estable.bin /var/www/racktables/plugins/

sudo cp logo.png /var/www/racktables/wwwroot/pix/
sudo cp not.gif /var/www/racktables/wwwroot/pix/

sudo cp js/ /var/www/racktables/plugins/ -r
sudo cp css/ /var/www/racktables/plugins/ -r
```

#### Set permissiones
This is needed for topologies storage
```bash
cd /var/www/racktables/wwwroot/
sudo chown www-data:www-data pix/ -R
```

#### SQL ReadOnly User

```mysql
create user 'viewRT'@'%' identified by 'viewRT';
grant select,lock tables,show view on racktables.* to 'viewRT'@'%';
```

## TODO

1. Migrate the python-core to Python3.x
2. Implement the new RackTables plugin-architecture in the file `graph.php`.
3. Get rid of `graphviz` and try to manage all the topologies natively through `networkx`. This will make the code a lot cleaner.