<?php 

	$songQuery = mysqli_query($con, "SELECT id FROM songs ORDER BY RAND() LIMIT 10");

	$resultArray = array();

	while($row = mysqli_fetch_array($songQuery)){
		array_push($resultArray , $row['id']);
	}

	$jsonArray = json_encode($resultArray);

 ?>

 <script>
	

	$(document).ready(function(){
		var newPlaylist = <?php echo  $jsonArray; ?>;   //id's of 10 current songs in playlist
		audioElement = new Audio();
		setTrack(newPlaylist[0], newPlaylist, false);
		updateVolumeProgressBar(audioElement.audio);	//call volume level when the page starts

		$("#nowPlayingBarContainer").on("mousedown touchdown mousemovw touchmove", function(e){
			e.preventDefault();
		});

		//playback slider reacting to mouse input

		$(".playbackBar .progressBar").mousedown(function(){
			mouseDown = true;
		});

		$(".playbackBar .progressBar").mousemove(function(e){
			if(mouseDown){
				timeFromOffset(e , this);
				//set time of progress bar to relative positoin	
			}
		});

		$(".playbackBar .progressBar").mouseup(function(e){
			timeFromOffset(e , this);
		});

		//Volume slider reacting to mouse input

		$(".volumeBar .progressBar").mousedown(function(){
			mouseDown = true;
		});

		$(".volumeBar .progressBar").mousemove(function(e){
			if(mouseDown){
				var percentage = e.offsetX / $(this).width();
				if(percentage >= 0 && percentage <=1){
					audioElement.audio.volume = percentage;	
				}
				
			}
		});

		$(".volumeBar .progressBar").mouseup(function(e){
			var percentage = e.offsetX / $(this).width();
			if(percentage >= 0 && percentage <=1){
				audioElement.audio.volume = percentage;	
			}
		});


		$(document).mouseup(function(){
			mouseDown = false;
		});

	});

	function timeFromOffset(mouse, progressBar){
		var percentage = mouse.offsetX / $(progressBar).width() * 100; 
		var seconds = audioElement.audio.duration * (percentage / 100);
		audioElement.setTime(seconds);
	}

	function nextSong(){

		if(repeat == true){
			audioElement.setTime(0);
			playSong();
			return;
		}

		if (currentIndex == currentPlaylist.length - 1){
			currentIndex = 1;
		}else{
			currentIndex++;
		}
		var trackToPlay = shuffle ? shufflePlaylist[currentIndex] : currentPlaylist[currentIndex];
		setTrack(trackToPlay, currentPlaylist, true);
	}

	function previousSong(){
		if(audioElement.audio.currentTime >= 3 || currentIndex == 0){
			audioElement.setTime(0);
		}else{
			currentIndex--;
		}
		var trackToPlay = shuffle ? shufflePlaylist[currentIndex] : currentPlaylist[currentIndex];
		setTrack(trackToPlay, currentPlaylist, true);
	}

	function setRepeat(){
		repeat = !repeat;
		var imageName = repeat ? "repeat-active.png" : "repeat.png";
		$(".controlButton.repeat img").attr("src" , "assets/images/icons/" + imageName);
	}

	function setMute(){
		audioElement.audio.muted = !audioElement.audio.muted;
		var imageName = audioElement.audio.muted ? "volume-mute.png" : "volume.png";
		$(".controlButton.volume img").attr("src" , "assets/images/icons/" + imageName);
	}

	function setShuffle(){
		shuffle = !shuffle;
		var imageName = shuffle ? "shuffle-active.png" : "shuffle.png";
		$(".controlButton.shuffle img").attr("src" , "assets/images/icons/" + imageName);

		if(shuffle == true){
			shufflePlaylist = shuffleArray(shufflePlaylist);
			currentIndex = shufflePlaylist.indexOf(audioElement.currentlyPlaying.id);
		}else{
			currentIndex = currentPlaylist.indexOf(audioElement.currentlyPlaying.id);
		}
	}

	function shuffleArray(array) {
    var j, x, i;
	    for (i = array.length - 1; i > 0; i--) {
	        j = Math.floor(Math.random() * (i + 1));
	        x = array[i];		//otherwise original value is lost while swapping
	        array[i] = array[j];	//swap a[i]'s value with a random value a[j]'
	        array[j] = x;
	    }
    	return array;
	}	

	function setTrack(trackId, newPlaylist, play){

		if(newPlaylist != currentPlaylist){
			currentPlaylist = newPlaylist;
			shufflePlaylist = currentPlaylist.slice(); //retruns copy of array without altering original 'currentPlaylist'
			shuffleArray(shufflePlaylist);
		}

		if(shuffle == true){
			currentIndex = shufflePlaylist.indexOf(trackId);	 // variable to keep track of where we are in the playlist
		}else{
			currentIndex = currentPlaylist.indexOf(trackId);
		}
		
		pauseSong();

		$.post("includes/handlers/ajax/getSongJson.php" , {songId : trackId} , function(data){

			var track = JSON.parse(data);

			$(".trackName span").text(track.title);

			$.post("includes/handlers/ajax/getArtistJson.php" , {artistId : track.artist} , function(data){
				var artist = JSON.parse(data);
				$(".artistName span").text(artist.name);
			});

			$.post("includes/handlers/ajax/getAlbumJson.php" , {albumId : track.album} , function(data){
				var album = JSON.parse(data);
				$(".albumLink img").attr("src" , album.artworkPath);
			});

			console.log(track);
			audioElement.setTrack(track);

			if(play == true){
				playSong();
			}
		});

		//audioElement.setTrack("assets/music/bensound-acousticbreeze.mp3");   //test music

		
	}

	function playSong(){

		if(audioElement.audio.currentTime == 0){
			$.post("includes/handlers/ajax/updatePlays.php" , {songId : audioElement.currentlyPlaying.id});
		}

		$(".controlButton.play").hide();
		$(".controlButton.pause").show();
		audioElement.play();
	}

	function pauseSong(){
		$(".controlButton.play").show();
		$(".controlButton.pause").hide();
		audioElement.pause();
	}


</script>


<div id="nowPlayingBarContainer">
	
	<div id="nowPlayingBar">

		<div id="nowPlayingLeft">
			<div class="content">
				<span class="albumLink">
					<img src="https://i.ytimg.com/vi/rb8Y38eilRM/maxresdefault.jpg" class="albumArtwork">
				</span>

				<div class="trackInfo">

					<span class="trackName">
						<span></span>
					</span>

					<span class="artistName">
						<span></span>
					</span>

				</div>
			</div>
		</div>

		<div id="nowPlayingCenter">
			<div class="content playerControls">
				<div class="buttons">
					<button class="controlButton shuffle" title="Shuffle button">
						<img src="assets/images/icons/shuffle.png" alt="shuffle" onclick="setShuffle()">
					</button>

					<button class="controlButton previous" title="previous button" onclick="previousSong()">
						<img src="assets/images/icons/previous.png" alt="previous">
					</button>

					<button class="controlButton play" title="play button" onclick="playSong()">
						<img src="assets/images/icons/play.png" alt="play">
					</button>

					<button class="controlButton pause" title="pause button" style="display:none;" onclick="pauseSong()">
						<img src="assets/images/icons/pause.png" alt="pause">
					</button>

					<button class="controlButton next" title="next button" onclick="nextSong()">
						<img src="assets/images/icons/next.png" alt="next">
					</button>

					<button class="controlButton repeat" title="repeat button" onclick="setRepeat()">
						<img src="assets/images/icons/repeat.png" alt="repeat">
					</button>
				</div>

				<div class="playbackBar">
					<span class="progressTime current">0:00</span>
					<div class="progressBar">
						<div class="progressBarBg">
							<div class="progress"></div>
						</div>	

					</div>
					<span class="progressTime remaining"></span>
				</div>

			</div>
		</div>

		<div id="nowPlayingRight">
			<div class="volumeBar">

				<button class="controlButton volume" title="Volume button" onclick="setMute()">
					<img src="assets/images/icons/volume.png" alt="Volume">
				</button>

				<div class="progressBar">
					<div class="progressBarBg">
						<div class="progress"></div>
					</div>	

				</div>
				
			</div>
		</div>


		
	</div>
</div>