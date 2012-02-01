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
			
			print("project " + obj.name + " - id: " + obj._id);
			//build stat object
			stat_item = {
				"project_id" : obj._id,
				"time" : min_sec,
				"sessions" : new Array(),
				"events" : new Array()
			}
			
			//******************************************************************
			m = function() { emit(this.code, { count: 1, sum: this.value, datatype: this.datatype }); }
			r = function(key, vals) { 
				var event_val = { count: 0, sum: 0, datatype: "", min: null, max: null };
				for(var k in vals)
				{
					event_val.count += vals[k].count;
					event_val.datatype = vals[k].datatype
					if(vals[k].datatype == "integer" || vals[k].datatype == "float")
					{
						if(!isNaN(vals[k].sum)) {
							event_val.sum += vals[k].sum;
							
							//min
							if(event_val.min == null) {
								event_val.min = vals[k].sum;
							}
							else {
								if(vals[k].sum < event_val.min)
									event_val.min = vals[k].sum;									
							}
							//max
							if(event_val.max == null) {
								event_val.max = vals[k].sum;
							}
							else {
								if(vals[k].sum > event_val.max)
									event_val.max = vals[k].sum;									
							}
						}
					}
				}
				return event_val;
			}
			f = function(key, val)
			{
				val.avg = val.sum / val.count;
				return val;
			}
			res = db.sessions_events.mapReduce(m, r, {query : {"project_id" : obj._id, "time" : { $gte: min_sec, $lte: max_sec }}, out: { inline : 1}, finalize: f});
			res.results.forEach(
				function(obj) {
					stat_item["events"].push({"code" : String(obj._id), "data" : obj.value, "values" : new Array()});
					print("code " + obj._id + ": " + obj.value.sum + " - " + obj.value.count);
				}
			);
			//******************************************************************
			
			//******************************************************************
			//map reduce for single string values
			m1 = function() { emit( {code: this.code, value: this.value}, { count: 1, code: this.code, datatype: this.datatype }); }
			r1 = function(key, vals) {
				var event_val_1 = { code: "", count: 0, datatype: "string" };
				for(var k in vals)
				{
					event_val_1.code = vals[k].code;
					event_val_1.datatype = vals[k].datatype;
					event_val_1.count += vals[k].count;
				}
				return event_val_1;
			}
			res1 = db.sessions_events.mapReduce(m1, r1, {query : {"project_id" : obj._id, "time" : { $gte: min_sec, $lte: max_sec }, "datatype" : { $in : ["string", "boolean"]}}, out: { inline : 1}});
			res1.results.forEach(
				function(obj) {
					print("event: " + obj._id.value + " -> " + obj.value.count);
					for (var i=0; i < stat_item["events"].length; i++) {
						if(stat_item["events"][i].code == obj.value.code)
						{
							switch(obj.value.datatype)
							{
								case "string":
									stat_item["events"][i].values.push({"value" : String(obj._id.value), "count" : obj.value.count});
									break;
								case "boolean":
									stat_item["events"][i].values.push({"value" : String(obj._id.value) == "1" ? "true" : "false", "count" : obj.value.count});
									break;
							}
						}
					};
				}
			);
			//******************************************************************
			
			//******************************************************************
			//map reduce for arrays
			m_array = function(){
				for(var key in this.value){
					if(this.value[key].constructor === String)
						emit( {code: key, value: this.value[key], event_code : this.code}, { count: 1, code: key, type: "string" });
					else
						emit( {code: key, event_code : this.code}, { count: 1, code: key, type: "numeric", sum: this.value[key] });
				}
			};
			r_array = function( key , values ) {
				var event_val_array = { code: "", count: 0, type : "string", min: null, max: null, sum: 0, avg : 0 };
				for(var k in values)
				{
					event_val_array.code = values[k].code;
					event_val_array.count += values[k].count;
					event_val_array.type = values[k].type;
					
					if(!isNaN(values[k].sum)) {
						
						event_val_array.sum += values[k].sum;
						
						//min
						if(event_val_array.min == null) {
							event_val_array.min = values[k].sum;
						}
						else {
							if(values[k].sum < event_val_array.min)
								event_val_array.min = values[k].sum;									
						}
						//max
						if(event_val_array.max == null) {
							event_val_array.max = values[k].sum;
						}
						else {
							if(values[k].sum > event_val_array.max)
								event_val_array.max = values[k].sum;									
						}
					}
				}
				return event_val_array;
			};
			f_array = function(key, val) {
				if(!isNaN(val.sum)) {
					val.avg = val.sum / val.count;
				}
				return val;
			};
			res_array = db.sessions_events.mapReduce(m_array, r_array, {query : {"project_id" : obj._id, "time" : { $gte: min_sec, $lte: max_sec }, "datatype" : "array"}, out: { inline : 1}, finalize: f_array});
			res_array.results.forEach(
				function(obj) {
					print("event: " + obj._id.event_code + " -> " + obj._id.code + " -> " + obj._id.value + " -> " + obj.value.count);
					for (var i=0; i < stat_item["events"].length; i++) {
						if(stat_item["events"][i].code == obj._id.event_code)
						{
							if(obj.value.type == "string")
								stat_item["events"][i].values.push({ "option" : obj._id.code, "value" : String(obj._id.value), "count" : obj.value.count});
							else
								stat_item["events"][i].values.push({ "option" : obj._id.code, "count" : obj.value.count, "sum" : obj.value.sum, "min" : obj.value.min, "max" : obj.value.max, "avg" : obj.value.avg });
						}
					}
				}
			);
			//******************************************************************
			
			//update or create the stat object
			stat_item_db = db.projects_stats.findOne({"project_id" : obj._id, "time" : min_sec});
			if(stat_item_db == undefined)
				db.projects_stats.insert(stat_item);
			else
				db.projects_stats.update({"_id" : stat_item_db._id}, {"$set" : { "events" :  stat_item["events"]}});
		}
	);
}