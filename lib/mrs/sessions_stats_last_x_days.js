var currentTime = new Date();
var month = currentTime.getUTCMonth();
var day = currentTime.getUTCDate();
var year = currentTime.getUTCFullYear();
var start = new Date(Date.UTC(year, month, day, 0, 0, 0, 0));
var stop = new Date(Date.UTC(year, month, day, 0, 0, 0, 0));
start.setDate(start.getDate() + 3);
stop.setDate(stop.getDate() + 2);

//last 10 days, starting 2 days ahead GMT
for (j=0; j <= 14; j++)
{
	start.setDate(start.getDate() - 1);
	stop.setDate(stop.getDate() - 1);
	
	var max_sec = start.getTime()/1000;
	var min_sec = stop.getTime()/1000;
	
	print("from: " + new Date(min_sec * 1000).toUTCString() + " to: " + new Date(max_sec * 1000).toUTCString());
	
	db.projects.find().forEach (
		function(obj) {
			
			print("project id: " + obj._id);

			stat_item = {
				"project_id" : obj._id,
				"time" : min_sec,
				"sessions" : new Array(),
				"events" : new Array()
			}
			
			//count sessions per project
			numsessions = db.sessions.count({"project_id" : obj._id, "time_start" : { $gte: min_sec, $lte: max_sec }});
			print("sessions:" + numsessions);
			stat_item["sessions"]["num"] = numsessions;

			//num for each version using a map reduce
			stat_item["sessions"]["versions"] = new Array();
			m = function() { emit(this.version, 1); }
			r = function(key, vals) { 
				var sum=0;
				for(var i in vals) sum += vals[i];
				return sum;
			}
			res = db.sessions.mapReduce(m, r, { query : {"project_id" : obj._id, "time_start" : { $gte: min_sec, $lte: max_sec }}, out: { inline : 1} });
			res.results.forEach(
				function(obj) {
					stat_item["sessions"]["versions"].push({"val" : String(obj._id), "num" : obj.value});
					print("version " + obj._id + ": " + obj.value);
				}
			);
			print("map reduce exec time: " + res.timeMillis);

			//update or create the stat object
			stat_item_db = db.projects_stats.findOne({"project_id" : obj._id, "time" : min_sec});
			if(stat_item_db == undefined)
				db.projects_stats.insert(stat_item);
			else
				db.projects_stats.update({"_id" : stat_item_db._id}, {$set : { "sessions" : stat_item["sessions"]}});
		
		}
	);
}