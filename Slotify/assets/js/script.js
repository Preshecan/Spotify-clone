var currentPlaylist = [];
var shufflePlaylist = [];
var tempPlaylist = [];
var audioElement;
var mouseDown = false;
var currentIndex = 0;
var repeat = false;
var shuffle = false;

function formatTime(seconds){			//make time appear in minutes and seconds instead of seconds to a decimal
	var time = Math.round(seconds);
	var minutes = Math.floor(time/60);  //Math.floor rounds number down
	var seconds = time - minutes*60;
	var extraZero = (seconds < 10) ? "0" : ""; //add extra zero if seconds is a single digit number
	
	return minutes + ":" + extraZero + seconds;
}

function updateTimeProgressBar(audio){
	$(".progressTime.current").text(formatTime(audio.currentTime));
	$(".progressTime.remaining").text(formatTime(audio.duration - audio.currentTime));

	var progress = audio.currentTime / audio.duration * 100;
	$(".playbackBar .progress").css("width", progress + "%");
}

function updateVolumeProgressBar(audio){	//update volume level appearance
	var volume = audio.volume * 100;
	$(".volumeBar .progress").css("width", volume + "%");
}


function Audio(){

	this.currentlyPlaying;
	this.audio = document.createElement('audio');

	this.audio.addEventListener("ended" , function(){
		nextSong();
	});

	

	this.audio.addEventListener("canplay" , function(){
		var duration = formatTime(this.duration); //this refers to the audio object
		$(".progressTime.remaining").text(duration); 
	});

	this.audio.addEventListener("timeupdate" , function(){
		if(this.duration){
			updateTimeProgressBar(this);
		}
	});

	this.audio.addEventListener("volumechange" , function(){	
		updateVolumeProgressBar(this);		
	});

	this.setTrack = function(track){
		this.currentlyPlaying = track;
		this.audio.src = track.path;
	}

	this.play = function(){
		this.audio.play();
	}

	this.pause = function(){
		this.audio.pause();
	}

	this.setTime = function(seconds){
		this.audio.currentTime = seconds;
	}

}