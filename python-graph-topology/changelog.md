# Name: draw_topo_02.py
# Version: 3.0
# Author: Lucas Aimaretto
# email: laimaretto@gmail.com

- 3.0: implemented OSM with links and distance in KM.
- 2.9: implemented OpenStreeMap on graph.php and the function fnc_build_osm(global_dict)
- 2.8: migrated to python3
- 2.7: separation of graph functions into fnc_build_graphviz(), fnc_build_graphml(), fnc_build_graphnx(), rename to topoGen.py
- 2.6: fnc_graphml()
- 2.5: rewrite of functions, using Pandas to manipiulate data.
- 2.4: full custimoization via argv[]. Different color depending con Ref_Order
- 2.3: implementing argv to pass parameters in line. change \n to &#92;n in port an router when mode 1,2,3
- 2.2: Option to graph nodes with the names, only.
- 2.1: Bug regarding getting the system IP of router: fnc_build_query_attributes(.)
	   TxType now considered when MIX TX is needed (ie: DWDM+MW)
- 2.0: Change Name of file to match Claro's format.
- 1.9: Ring topology now available
- 1.8: Includes transmission with a list
- 1.7: Different colors for MR and HR
- 1.3: Including FO and ARSAT as possible labels for links
- 1.2: Reducing font size of link's labels
- 1.1: Distinction between High, Mid and Low Ran
- 0.8: Asked whether to graph LAGs or not
	   TBD: consider when link label is something else than "LAG", "Hairpin" or "".
- 0.7: fnc_build_query_objetos(.) modify to further filter out objects with tags (and no ipvnet)
	   fnc_build_query_interfaces(.) to bring IPs and interfaces
	   fnc_build_query_connections(.) now only brings connections and no IPs
	   fnc_cross_conn_inter(.) finds out IP for connections
- 0.6: If no SFP, no speed can be obtained. Then "No SFP" is shown. If CES, ATM or ASAP, then "No ETH" is shown.
- 0.5: Including system IP, sync order, color for not integrated routers.
- 0.4: Included sync-e reference to each port. If not abailable, then N/A is shown.
	   For this, a sync_dict is created.
- 0.3: Implementing of IP/PORT/Speed information. Change on function
	   fnc_build_query_connections SQL's query
- 0.2: Reorder of functions; cleaning of code.
- 0.1: first draft
	   This version will graph any topology based on the tags of the routers.
















