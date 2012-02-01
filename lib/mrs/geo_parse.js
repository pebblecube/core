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
			//build stat object
			stat_item = {
				"project_id" : obj._id,
				"time" : min_sec,
				"events" : new Array(),
				"sessions" : new Array()
			}
			
			//******************************************************************
			m = function() { emit(this.client_ip_geo.country_code, { count: 1, country_name: this.client_ip_geo.country_name }); }
			r = function(key, vals) { 
				var event_val = { count: 0, country_name: "" };
				for(var k in vals)
				{
					event_val.count += vals[k].count;
					event_val.country_name = vals[k].country_name
				}
				return event_val;
			}
			res = db.sessions_events.mapReduce(m, r, {query : {"project_id" : obj._id, "client_ip_geo" : { $ne : false }, "client_ip_geo" : { $ne : null }, "time" : { $gte: min_sec, $lte: max_sec }}, out: { inline : 1}});
			res.results.forEach(
				function(obj) {
					stat_item["events"].push({"country_code" : String(obj._id), "count" : obj.value.count, "country_name" : obj.value.country_name });
				}
			);
			print("map reduce exec time: " + (res.timeMillis / 1000) + " secs");
			//******************************************************************
			
			//******************************************************************
			m = function() { emit(this.client_ip_geo.country_code, { count: 1, country_name: this.client_ip_geo.country_name }); }
			r = function(key, vals) { 
				var event_val = { count: 0, country_name: "" };
				for(var k in vals)
				{
					event_val.count += vals[k].count;
					event_val.country_name = vals[k].country_name
				}
				return event_val;
			}
			res = db.sessions.mapReduce(m, r, {query : {"project_id" : obj._id, "client_ip_geo" : { $ne : false }, "client_ip_geo" : { $ne : null }, "time_start" : { $gte: min_sec, $lte: max_sec }}, out: { inline : 1} });
			res.results.forEach(
				function(obj) {
					stat_item["sessions"].push({"country_code" : String(obj._id), "count" : obj.value.count, "country_name" : obj.value.country_name });
				}
			);
			print("map reduce exec time: " + (res.timeMillis / 1000) + " secs");
			//******************************************************************
			
			//update or create the stat object
			stat_item_db = db.projects_stats_geo.findOne({"project_id" : obj._id, "time" : min_sec});
			if(stat_item_db == undefined)
				db.projects_stats_geo.insert(stat_item);
			else
				db.projects_stats_geo.update({"_id" : stat_item_db._id}, {"$set" : { "events" : stat_item["events"], "sessions" : stat_item["sessions"]}});
		}
	);
}