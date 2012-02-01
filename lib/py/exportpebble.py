import shlex
import zipfile
import subprocess
import sys
import time
import os
import datetime
import pymongo

www_stats_path = ""
mongo_bin_folder = ""
db = ""

def export_collection(project_id, base_path, base_filename, collection, name, fields, query, start_time):
	export_sessions_command = mongo_bin_folder + 'mongoexport --csv -f '+ fields +' -d pebblecube -c '+ collection +' -q "'+ query +'" -o ' + base_path + '/' + name + base_filename
	print(export_sessions_command)
	args = shlex.split(export_sessions_command)
	p = subprocess.Popen(args, stderr=subprocess.STDOUT, stdout=subprocess.PIPE, shell=False)
	p.wait()
	print(str(p.stdout.readlines()))
	if (os.path.isfile(base_path + '/' + name + base_filename) == True):
		if (os.path.isfile(base_path + '/' + name + base_filename + ".zip") == True):
			os.remove(base_path + '/' + name + base_filename + ".zip")	
	myzip = zipfile.ZipFile(base_path + '/' + name + base_filename + ".zip", 'w')
	myzip.write(base_path + '/' + name + base_filename, os.path.basename(base_path + '/' + name + base_filename))
	myzip.close()
	#remove csv
	os.remove(base_path + '/' + name + base_filename)
	#add file to collection
	fileexport = {"time": start_time, "name": name + base_filename + ".zip", "collection" : collection, "project_id" : pymongo.helpers.bson.objectid.ObjectId(project_id)}
	db.projects_stats_exports.update({"project_id": pymongo.helpers.bson.objectid.ObjectId(project_id), "collection" : collection, "time": start_time}, fileexport, True);

def export_project(project_id, start_time, stop_time):
	projects_stats_path = www_stats_path + "/" + str(project_id)
	projects_stats_file_postfix = "-" + str(start_time) + ".csv" # -Epoch.csv
	#check folder exists
	if os.path.exists(projects_stats_path) != True:
		os.mkdir(projects_stats_path)
	#export sessions using mongo export
	export_collection(project_id, projects_stats_path, projects_stats_file_postfix, "sessions", "sessions", "client_ip,time_start,time_stop,version", "{\\\"project_id\\\" : ObjectId(\\\""+ str(project_id) +"\\\"), \\\"time_start\\\" : { $gte: "+ str(start_time) +", $lte: "+ str(stop_time) +" }}", start_time)
	#events
	export_collection(project_id, projects_stats_path, projects_stats_file_postfix, "sessions_events", "events", "code,value,time,client_ip", "{\\\"project_id\\\" : ObjectId(\\\""+ str(project_id) +"\\\"), \\\"time\\\" : { $gte: "+ str(start_time) +", $lte: "+ str(stop_time) +" }}", start_time)
	
