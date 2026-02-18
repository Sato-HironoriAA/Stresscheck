$(document).ready(function(){
	var aTags = $('a');
	aTags.each(function(){
		var target = $(this).attr('target');
		var url = $(this).attr('href');
		if (target != '_blank' && url != '#' && url) {
			$(this).removeAttr('href');
			$(this).click(function(){
				location.href = url;
			});
		}
	});

	//init
	var stageX;
	var cardWidth = 976;
	var cardNum = 0;
	var moveFlag = 0;
	var choice = 0;
	var res = "";
	var targetClass = "";
	var currentChapter = 0;
	var previousChapter = 0;
	var parentNum = 0;
	var count = 0;


	backButton();
	$('#progressbar').append('<ul></ul>');
	$('form').append('<div id="resultData"></div>');
	$('input#choice1').hide();

	//stage slide

	$('#contents .next').click(function(){
		if (moveFlag == 0) {
			slideNext();
		}
	});

	$('#contents .btnNext').click(function(){
		if (moveFlag == 0) {
			var choice = 0;
			if ($(this).attr('class').indexOf('1') != -1) {
				choice = 1;
			} else if ($(this).attr('class').indexOf('2') != -1) {
				choice = 2;
			} else if ($(this).attr('class').indexOf('3') != -1) {
				choice = 3;
			} else if ($(this).attr('class').indexOf('4') != -1) {
				choice = 4;
			}
			setResult(choice);
			slideNext();
		}
	});

	$('#contents .prev,#contents .btnPrev').click(function(){
		if (moveFlag == 0) {
			slidePrev();
		}
	});

	$('#contents .goTitle,#contents .btnTitle').click(function(){
		if (moveFlag == 0) {
			slideTitle();
		}
	});

	$('.btnPrint, .resultBtn .print').click(function(){
		window.print();
		return false;
	});

	function slideNext(){
		moveFlag = 1;
		$('.stage').animate({left: '-='+cardWidth+'px'},500,'easeInOutExpo',function(){
			moveFlag = 0;
			setCardNumber();
		});
	}

	function slidePrev(){
		moveFlag = 1;
		stageX = $('.stage').position().left;
		stageX += cardWidth;
		if (stageX > 0) {
			stageX = 0;
		}
		$('.stage').animate({left: stageX},500,'easeInOutExpo',function(){
			moveFlag = 0;
			setCardNumber();
		});
	}

	function slideTitle(){
		moveFlag = 1;
		$('.stage').animate({left: '0px'},1000,'easeInOutExpo',function(){
			moveFlag = 0;
			setCardNumber();
		});
	}

	function setCardNumber(){
		stageX = $('.stage').position().left;
		cardNum = -(Math.floor(stageX / cardWidth));
		backButton();

		// set result data
		count = 0;
		$('#resultData').empty();
		for (var i=0; i < result.length; i++){
			if (result[i] != 0) {
				$('#resultData').append('<input type="hidden" name="result[]" value="'+result[i]+'" />');
				count ++;
			}
		}
		$('form input').fadeIn();

		// debug
		for (var i=0; i < result.length; i++){
			res += result[i]+":";
		}
		res = "chapter:"+currentChapter+" - "+"card:"+cardNum+" - "+res
		$('#debugger').html(res);
		res = "";
	}

	// back button
	function backButton(){
		if (cardNum == 0) {
			$('#contents .btnPrev').animate({bottom: '-40px'},500,'easeOutExpo');
			$('#contents .btnTitle').animate({bottom: '-40px'},500,'easeOutExpo');
		} else {
			$('#contents .btnPrev').animate({bottom: '20px'},500,'easeOutExpo');
			$('#contents .btnTitle').animate({bottom: '20px'},500,'easeOutExpo');
		}
	}


	// debugger
	function setDebugger(){
		$('body').append('<div id="debugger">&nbsp;</div>');
	}

	/* set parent */
	function outParent(){
		$('#parent').animate({top: '-50px'},500,'easeOutExpo',function(){
			setParent();
		});
	}

	function setParent(){
		$('#parent').empty();
		$('#parent').append(parentImg[currentChapter]);
		inParent();
	}

	function inParent(){
		$('#parent').animate({top: '0'},500,'easeOutExpo');
	}

	// stress check
	var result = new Array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

	function setResult(choice){
		result[cardNum] = choice;
	}
});
