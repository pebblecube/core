function get_start_stop(days) {
	currentTime = new Date();
	month = currentTime.getUTCMonth();
	day = currentTime.getUTCDate();
	year = currentTime.getUTCFullYear();
	start = new Date(Date.UTC(year, month, day, 0, 0, 0, 0));
	stop = new Date(Date.UTC(year, month, day, 0, 0, 0, 0));
	stop.setDate(stop.getDate() - days);
	max_sec = start.getTime()/1000;
	min_sec = stop.getTime()/1000;
	return ({ "start" : min_sec, "stop" : max_sec});
}

function get_min(group, prj_id) {
	time_filter = get_start_stop(group.days + 1);
	min_value = 0;
	db.projects_stats.find({
			"project_id" : prj_id,
			"events.code" : group.event,
			"time" : { $gte: time_filter.start, $lte: time_filter.stop }
		}).sort( { "events.data.min" : 1 } ).limit(1).forEach(
		function(obj) {
			for (var i=0; i < obj.events.length; i++) {
				if(obj.events[i].code == group.event) {
					min_value = obj.events[i].data.min;
					break;
				}
			}
		});
	return min_value;
}

function get_max(group, prj_id) {
	time_filter = get_start_stop(group.days + 1);
	max_value = 0;
	db.projects_stats.find({
			"project_id" : prj_id,
			"events.code" : group.event,
			"time" : { $gte: time_filter.start, $lte: time_filter.stop }
		}).sort( { "events.data.max" : -1 } ).limit(1).forEach(
		function(obj) {
			for (var i=0; i < obj.events.length; i++) {
				if(obj.events[i].code == group.event) {
					max_value = obj.events[i].data.max;
					break;
				}
			}
		});
	return max_value;
}

function get_sum(group, prj_id) {
	time_filter = get_start_stop(group.days + 1);
	value_sum = 0;
	db.projects_stats.find({
			"project_id" : prj_id,
			"events.code" : group.event,
			"time" : { $gte: time_filter.start, $lte: time_filter.stop }
		}).forEach(
			function(obj) {
				for (var i=0; i < obj.events.length; i++) {
					if(obj.events[i].code == group.event) {
						value_sum += obj.events[i].data.sum;
					}
				}
			}
		);
	return value_sum;
}

function get_count(group, prj_id) {
	time_filter = get_start_stop(group.days + 1);
	value_count = 0;
	db.projects_stats.find({
			"project_id" : prj_id,
			"events.code" : group.event,
			"time" : { $gte: time_filter.start, $lte: time_filter.stop }
		}).forEach(
			function(obj) {
				for (var i=0; i < obj.events.length; i++) {
					if(obj.events[i].code == group.event) {
						value_count += obj.events[i].data.count;
					}
				}
			}
		);
	return value_count;
}

function get_avg(group, prj_id) {
	return (get_sum(group, prj_id) / get_count(group, prj_id));
}

//for each project check the grouping expressions
db.projects.find().forEach (
		function(obj) {
			print("project id: " + obj._id);
			if(obj.functions != null)
			{
				for (f=0; f < obj.functions.length; f++) {
					func = obj.functions[f];
					print("function: " + func.code);
					for (g=0; g < obj.functions[f].groups.length; g++) {
						group = obj.functions[f].groups[g];
						print("group: " + group.formula);
						switch(group.group) {
							case "min":
								group.value = get_min(group, obj._id);
								break;
							case "max":
								group.value = get_max(group, obj._id);
								break;
							case "sum":
								group.value = get_sum(group, obj._id);
								break;
							case "count":
								group.value = get_count(group, obj._id);
								break;
							case "avg":
								group.value = get_avg(group, obj._id);
								break;
						}
						print("value: " + group.value);
					}
				}
			}
			db.projects.save(obj);
		});