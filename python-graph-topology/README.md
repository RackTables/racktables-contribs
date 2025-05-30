# README #

## Configuration of graphTopo plugin

Follow these steps:

### Global libs

These global libs might be needed in case you face error while installing the python-libs within the venv. Everything has been tested in a recent Ubuntu 24.04 server. Use as needed.

```bash
sudo apt install python3.12-venv
sudo apt install pkg-config
sudo apt install default-libmysqlclient-dev
sudo apt install python3-distutils-extra
```

### The virtual-env

The virtual environment will be created under the `/var/www/` folder. If you need to change the place of the venv, modify it accordingly in the file `plugin.php`.

```bash
cd /var/www/
sudo mkdir -p .venv_topoGen/
sudo python3 -m venv .venv_topoGen/
```

In order to be able to install libs within it, change the ownership of the venv to an appropiate user (probably the admin user).

```bash
sudo chown <USER>:www-data .venv_topoGen/ -R
```

### Libraries

The libraries will be installed within the venv.

```bash
cd /var/www/
source .venv_topoGen/bin/activate
pip3 install pandas==2.2.2
pip3 install numpy==1.26.4
pip3 install openpyxl==3.0.10
pip3 install networkx==2.5
pip3 install pyyed==1.3
pip3 install pyvis==0.1.9
pip3 install geopy==2.0.0
pip3 install folium==0.12.1
pip3 install mysqlclient==2.2.7
pip3 install PyYAML==6.0.2
deactivate
```

### Copy Files

All the files are stored under the folder `topoGen` inside the `plugins` folder of RackTables.

```bash
sudo mkdir /var/www/html/racktables/plugins/topoGen

sudo cp plugin.php         /var/www/html/racktables/plugins/topoGen/
sudo cp topoGen.py         /var/www/html/racktables/plugins/topoGen/
sudo cp functions.py       /var/www/html/racktables/plugins/topoGen/
sudo cp sql_definitions.py /var/www/html/racktables/plugins/topoGen/
sudo cp settings.yml       /var/www/html/racktables/plugins/topoGen/

sudo cp js/  /var/www/html/racktables/plugins/topoGen/ -r
sudo cp css/ /var/www/html/racktables/plugins/topoGen/ -r

sudo cp not.gif  /var/www/html/racktables/wwwroot/pix/
sudo cp logo.png /var/www/html/racktables/wwwroot/pix/
```

### Link 
This is needed so the `plugin.php` file can access the `topoGen.py`. This is so, because the root of RackTables is placed at the folder `wwwroot`.
```bash
cd /var/www/html/racktables/wwwroot/
sudo sudo ln ../plugins/ . -s
```

### Set permissions
This is needed for topologies storage and write access to de `plugins` folder.
```bash
cd /var/www/html/racktables/wwwroot/
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
- A topology is a group of nodes sharing the same `tag`. In order to plot a topology, all nodes be must share the same `tag`. Create a `tag` and assign it to each and every node.
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