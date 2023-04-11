# README #

## Configuration of graphTopo plugin

Follow these steps:

### System libraries

```bash
sudo apt install python3-mysqldb
sudo pip3 install -r requirements.txt
```

#### Versions of libraries

See the file `requirements.txt` for the versions of libraries that I'm using in my production installation. There is a bug with version `1.1.0` of Pandas, which is bypassed in here. If possible, try to respect the versions inside `requirements.txt`. If you face issues, let me know.

### Copy Files

All the files are stored under the folder `topoGen` inside the `plugins` folder of RackTables.

```bash
sudo cp plugin.php /var/www/racktables/plugins/topoGen/
sudo cp topoGen.py /var/www/racktables/plugins/topoGen/
sudo cp functions.py /var/www/racktables/plugins/topoGen/
sudo cp sql_definitions.py /var/www/racktables/plugins/topoGen/
sudo cp settings.yml /var/www/racktables/plugins/topoGen/

sudo cp not.gif /var/www/racktables/wwwroot/pix/

sudo cp js/ /var/www/racktables/plugins/topoGen/ -r
sudo cp css/ /var/www/racktables/plugins/topoGen/ -r
```

### Link 
This is needed so the `plugin.php` file can access the `topoGen.py`.
```bash
cd /var/www/racktables/wwwroot/
sudo sudo ln ../plugins/ . -s
```

### Set permissions
This is needed for topologies storage and write access to de `plugins` folder.
```bash
cd /var/www/racktables/wwwroot/
sudo chown www-data:www-data pix/ -R
sudo chown www-data:www-data plugins/
```

### SQL ReadOnly User

```mysql
create user 'viewRT'@'%' identified by 'viewRT';
grant select,lock tables,show view on racktables.* to 'viewRT'@'%';
```

##### Edit settings.yml
Setup connection to DB in file `settings.yml`.
```yaml
mysql:
  username: viewRT
  password: viewRT
  host: 127.0.0.1
  port: 3306
  database: racktables
```

## Usage & Customization

- To enable the plugin, follow RackTables instructions on how to enable a plugin. Go to `Main Page : Configuration : Plugins`.
- A topology is a group of nodes. In order to plot a topology, all nodes be must share the same `tag`. Create a `tag` and assign it to each and every node.
- Latitude and longitude. In order to set LAT/LON for nodes, create a string-type attribute, called `Int_LAT_LON`. Document LAT and LON as `lat:lon`.
- The colors of the nodes, is controlled by a custom attribute. Create a dictionary-type attribute called `HW function`. Inside of it add as many options as you wish. In the file `settings.yml` you will see a structure where you can map the color and the HW function.
```yml
colorScheme:
  hwFunction:
    High-Ran: '#3399ff'
    High-Ran-ABR: '#3399ff'
    Mid-Ran: '#ff99cc'
    AggEthernet: '#33ff33'
    FronteraPE: '#cccc00'
    TX: '#ffff00'
    CSR: '#99ccff'
```

## TODO

- Reorder code.