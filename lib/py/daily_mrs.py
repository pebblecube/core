#daily execution of the map reduces
import subprocess
import sys
import shlex
import pymongo
from pymongo import Connection
import memcache

mongo_bin_folder = ""
www_mrs_path = "/var/www/pebblecube/svn/lib/mrs"

def execute_mrs(filename):
    mrs_command = mongo_bin_folder + "mongo pebblecube " + www_mrs_path + "/" + filename
    args = shlex.split(mrs_command)
    p = subprocess.Popen(args, stderr=subprocess.STDOUT, stdout=subprocess.PIPE, shell=False)
    p.wait()
    print(str(p.stdout.readlines()))

#stats
execute_mrs("events_stats_last_x_days.js")
execute_mrs("sessions_stats_last_x_days.js")
execute_mrs("geo_parse.js")
execute_mrs("functions_parse.js")

#for each project deletes the memcached object
mc = memcache.Client(['127.0.0.1:11211'], debug=0)
connection = Connection('localhost', 27017)
db = connection.pebblecube
for prj in db.projects.find():
	mc.delete("project_{0}".format(prj['_id']))
	print("project_key_{0}_sig_{1}".format(prj['api_key'], prj['api_sig']))
	mc.delete("project_key_{0}_sig_{1}".format(prj['api_key'], prj['api_sig']))
