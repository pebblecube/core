import pygeoip
import pymongo
import json
from pymongo import Connection

geo_ip_path = "/usr/share/GeoIP/GeoIPCity.dat"

connection = Connection('localhost', 27017)
db = connection.pebblecube
for apilog in db.apilogs.find({"geo" : None}):
	try:
		gic = pygeoip.GeoIP(geo_ip_path)
		record = gic.record_by_addr(apilog["server"]["REMOTE_ADDR"])
		db.apilogs.update({"_id": apilog["_id"]}, {"$set": {"geo": record}})
	except:
		print "api error on ip: " + apilog["server"]["REMOTE_ADDR"]
		db.apilogs.update({"_id": apilog["_id"]}, {"$set": {"geo": False}})
		pass

for apilog in db.sessions_events.find({"client_ip_geo" : None}):
	try:
		gic = pygeoip.GeoIP(geo_ip_path)
		record = gic.record_by_addr(apilog["client_ip"])
		db.sessions_events.update({"_id": apilog["_id"]}, {"$set": {"client_ip_geo": record}})
	except:
		print "events error on ip: " + apilog["client_ip"]
		db.sessions_events.update({"_id": apilog["_id"]}, {"$set": {"client_ip_geo": False}})
		pass

for apilog in db.sessions.find({"client_ip_geo" : None}):
	try:
		gic = pygeoip.GeoIP(geo_ip_path)
		record = gic.record_by_addr(apilog["client_ip"])
		db.sessions.update({"_id": apilog["_id"]}, {"$set": {"client_ip_geo": record}})
	except:
		print "sessions error on ip: " + apilog["client_ip"]
		db.sessions.update({"_id": apilog["_id"]}, {"$set": {"client_ip_geo": False}})
		pass
