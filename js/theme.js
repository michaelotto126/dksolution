$(document).ready(function() {

/* =================================
   Sidebar tabs
   ================================= */
$('.sidebar-icon a:not(:first)').click(function (e) {
	e.preventDefault();
	$(this).tab('show');
});

/* =================================
   Show menu button on mobile
   ================================= */
$("#show-menu").click(function(e) {
	e.preventDefault();
	$("#wrapper").toggleClass("active");
});

/* =================================
   Custom file dialog
   ================================= */
$('.show-file-dialog').click(function(e) {
	e.preventDefault();
	var targetDialog = $(this).attr('href');
	$(targetDialog).click();
});

/* =================================
   Mini charts
   ================================= */
$(".sparkline-bar").sparkline('html', {
	type: 'bar',
	height: '50px',
	barWidth: 10,
	chartRangeMin: 10,
	zeroAxis: false,
	disableInteraction: true,
	barColor: '#f9df9e',
	negBarColor: '#e74c3c',
	stackedBarColor: [ '#f9df9e','#338fbe','#109618','#66aa00','#dd4477','#0099c6','#990099' ]
});

$(".sparkline-pie").sparkline('html', {
    type: 'pie',
    disableInteraction: true,
    offset: '-90',
    sliceColors: ['#5bb2d3','#ffce55','#C2454E']
});

/* =================================
   Equalize columns
   ================================= */
var maxHeight = 0;

$(".activity-feed-wrapper").each(function(){
   if ($(this).height() > maxHeight) { maxHeight = $(this).height(); }
});

$(".activity-feed-wrapper").height(maxHeight);

/* =================================
   Knobs
   ================================= */
$(".dial").knob({
	bgColor: '#e8e8e8',
	inputColor: '#2b3035',
	thickness: '.2',
	width: 75,
	height: 75,
	readOnly: true
});

/* =================================
   Calendar
   ================================= */
var date = new Date();
var d = date.getDate();
var m = date.getMonth();
var y = date.getFullYear();

$('#calendar').fullCalendar({
	editable: true,
	events: [
		{
			title: 'All Day Event',
			start: new Date(y, m, 1)
		},
		{
			title: 'Long Event',
			start: new Date(y, m, d-5),
			end: new Date(y, m, d-2)
		},
		{
			id: 999,
			title: 'Repeating Event',
			start: new Date(y, m, d-3, 16, 0),
			allDay: false
		},
		{
			id: 999,
			title: 'Repeating Event',
			start: new Date(y, m, d+4, 16, 0),
			allDay: false
		},
		{
			title: 'Meeting',
			start: new Date(y, m, d, 10, 30),
			allDay: false
		},
		{
			title: 'Lunch',
			start: new Date(y, m, d, 12, 0),
			end: new Date(y, m, d, 14, 0),
			allDay: false
		},
		{
			title: 'Birthday Party',
			start: new Date(y, m, d+1, 19, 0),
			end: new Date(y, m, d+1, 22, 30),
			allDay: false
		},
		{
			title: 'Click for Google',
			start: new Date(y, m, 28),
			end: new Date(y, m, 29),
			url: 'http://google.com/'
		}
	]
});

/* =================================
   2 Morris charts
   ================================= */
/*new Morris.Area({
	element: 'chart1',
	data: [
		{ year: '2001', x: 120, y: 27, z: 25 },
		{ year: '2002', x: 88, y: 46, z: 17 },
		{ year: '2003', x: 132, y: 25, z: 66 },
		{ year: '2004', x: 120, y: 33, z: 20 },
		{ year: '2005', x: 55, y: 50, z: 44 },
		{ year: '2006', x: 122, y: 27, z: 25 },
		{ year: '2007', x: 53, y: 120, z: 17 },
		{ year: '2008', x: 183, y: 25, z: 14 },
		{ year: '2009', x: 100, y: 33, z: 20 },
		{ year: '2010', x: 46, y: 50, z: 20 },
		{ year: '2011', x: 124, y: 25, z: 14 },
		{ year: '2012', x: 44, y: 33, z: 20 },
		{ year: '2013', x: 46, y: 50, z: 44 },
		{ year: '2014', x: 123, y: 27, z: 25 },
		{ year: '2015', x: 53, y: 46, z: 66 },
		{ year: '2016', x: 34, y: 25, z: 14 },
		{ year: '2017', x: 120, y: 27, z: 25 },
		{ year: '2018', x: 88, y: 46, z: 17 },
		{ year: '2019', x: 132, y: 25, z: 66 },
		{ year: '2020', x: 120, y: 33, z: 20 },
		{ year: '2021', x: 55, y: 50, z: 44 },
		{ year: '2022', x: 122, y: 27, z: 25 }
	],
	xkey: 'year',
	lineWidth: 0,
	grid: false,
	pointSize: 0,
	ykeys: ['x', 'y', 'z'],
	labels: ['Visits', 'Hits', 'Views'],
	behaveLikeLine: true,
	fillOpacity: 1,
	lineColors: ['#ffce55', '#379ca8', '#ee6969']
});

new Morris.Bar({
	element: 'chart2',
	data: [
		{ year: '2001', y: 27, z: 25 },
		{ year: '2002', y: 46, z: 17 },
		{ year: '2003', y: 25, z: 14 },
		{ year: '2004', y: 33, z: 20 },
		{ year: '2005', y: 50, z: 44 },
		{ year: '2006', y: 27, z: 25 },
		{ year: '2007', y: 46, z: 17 },
		{ year: '2008', y: 25, z: 14 },
		{ year: '2009', y: 33, z: 20 },
		{ year: '2010', y: 50, z: 44 }
	],
	xkey: 'year',
	grid: false,
	ykeys: ['y', 'z'],
	labels: ['Uptime', 'Downtime'],
	barColors: ['#6dba89', '#ed5564']
});*/

/* End scripts */
});