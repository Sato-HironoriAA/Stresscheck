//progress処理
function progress(num){
	if(num > 1 && num < 19){
		var pW = (num - 1) * (100 / 17);
		$('#progress span').animate({width: pW + '%'},500,'easeInOutExpo');
	}
	if(num > 19 && num < 49){
		var pW = (num - 19) * (100 / 29);
		$('#progress span').animate({width: pW + '%'},500,'easeInOutExpo');
	}
	if(num > 49 && num < 59){
		var pW = (num - 49) * (100 / 9);
		$('#progress span').animate({width: pW + '%'},500,'easeInOutExpo');
	}
	if(num > 59 && num < 62){
		var pW = (num - 59) * (100 / 2);
		$('#progress span').animate({width: pW + '%'},500,'easeInOutExpo');
	}
	if(num <= 1 || num == 19 || num == 49 || num == 59 || num == 62){
		$('#progress span').animate({width: '0%'},500,'easeInOutExpo');
	}
}

//header footer処理
function itemUp(){
	var itemNum = $('#contents').data('role') + 1;
	$('#contents').data('role', itemNum);

	//step1 cover
	if(itemNum == 1){
		$('#header .i1, #footer .i1').animate({left: -320},500,'easeInOutExpo', function(){
			$(this).css('left', 320);
		});
		$('#header .i2, #footer .i2').animate({left: 0},500,'easeInOutExpo');
	}

	//step1 question
	if(itemNum == 2){
		$('#header #step .s1, #progress').animate({left: 0},500,'easeInOutExpo');
	}

	//step2 cover
	if(itemNum == 19){
		$('#header #step .s1, #progress').animate({left: -320},500,'easeInOutExpo', function(){
			$(this).css('left', 320);
		});
	}

	//step2 question
	if(itemNum == 20){
		$('#header #step .s2, #progress').animate({left: 0},500,'easeInOutExpo');
	}

	//step3 cover
	if(itemNum == 49){
		$('#header #step .s2, #progress').animate({left: -320},500,'easeInOutExpo', function(){
			$(this).css('left', 320);
		});
	}

	//step3 question
	if(itemNum == 50){
		$('#header #step .s3, #progress').animate({left: 0},500,'easeInOutExpo');
	}

	//step4 cover
	if(itemNum == 59){
		$('#header #step .s3, #progress').animate({left: -320},500,'easeInOutExpo', function(){
			$(this).css('left', 320);
		});
	}

	//step4 question
	if(itemNum == 60){
		$('#header #step .s4, #progress').animate({left: 0},500,'easeInOutExpo');
	}

	//result
	if(itemNum == 62){
		$('#header #step .s4, #progress').animate({left: -320},500,'easeInOutExpo', function(){
			$(this).css('left', 320);
		});
	}
	progress(itemNum);
}
function itemDown(){
	var itemNum = $('#contents').data('role') - 1;
	$('#contents').data('role', itemNum);

	//cover
	if(itemNum == 0){
		$('#header .i2, #footer .i2').animate({left: 320},500,'easeInOutExpo');
		$('#header .i1, #footer .i1').css('left', -320).animate({left: 0},500,'easeInOutExpo');
	}

	//step1 cover
	if(itemNum == 1){
		$('#header #step .s1, #progress').animate({left: 320},500,'easeInOutExpo');
	}

	//step1 question
	if(itemNum == 18){
		$('#header #step .s1, #progress').css('left', -320).animate({left: 0},500,'easeInOutExpo');
	}

	//step2 cover
	if(itemNum == 19){
		$('#header #step .s2, #progress').animate({left: 320},500,'easeInOutExpo');
	}

	//step2 question
	if(itemNum == 48){
		$('#header #step .s2, #progress').css('left', -320).animate({left: 0},500,'easeInOutExpo');
	}

	//step3 cover
	if(itemNum == 49){
		$('#header #step .s3, #progress').animate({left: 320},500,'easeInOutExpo');
	}

	//step3 question
	if(itemNum == 58){
		$('#header #step .s3, #progress').css('left', -320).animate({left: 0},500,'easeInOutExpo');
	}

	//step4 cover
	if(itemNum == 59){
		$('#header #step .s4, #progress').animate({left: 320},500,'easeInOutExpo');
	}

	//step4 question
	if(itemNum == 61){
		$('#header #step .s4, #progress').css('left', -320).animate({left: 0},500,'easeInOutExpo');
	}

	progress(itemNum);
}
function itemTitle(){
	$('#contents').data('role', 0);
	$('#header .i2, #footer .i2').animate({left: 320},500,'easeInOutExpo', function(){
		$('#header #step li, #progress').css('left', 320);
	});
	$('#header .i1, #footer .i1').css('left', -320).animate({left: 0},500,'easeInOutExpo');
}


$(document).ready(function(){

	//setDebugger();

	// common

	// for ipad

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
	var cardWidth = 320;
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
			itemUp();
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
			itemUp();
		}
	});

	$('#contents .prev,#contents .btnPrev').click(function(){
		if (moveFlag == 0) {
			slidePrev();
			itemDown();
		}
	});

	$('#contents .goTitle,#contents .btnTitle').click(function(){
		if (moveFlag == 0) {
			delProgressBar();
			slideTitle();
			itemTitle();
		}
	});

	$('.btnPrint').click(function(){
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
		setChapter();
		backButton();

		// set result data

//		if (cardNum == 62){
			count = 0;
			$('#resultData').empty();
			for (var i=0; i < result.length; i++){
				if (result[i] != 0) {
					$('#resultData').append('<input type="hidden" name="result[]" value="'+result[i]+'" />');
					count ++;
				}
			}
			$('form input').fadeIn();
//		}
//		if (cardNum != 62){
//			$('input#choice1').fadeOut();
//		}

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


	// progress bar

	function outProgressDot(){
		$('#progressbar ul li').css("opacity",0.2);
		onProgressDot();
	}

	function delProgressDot(){
		$('#progressbar ul li').css("opacity",0.2);
	}

	function onProgressDot(){
		dotNum = 99;
		if (cardNum >= 1 && cardNum <= 18) {
			if (cardNum - 2 >= 0){
				dotNum = cardNum - 2;
			}
		}
		if (cardNum >= 19 && cardNum <= 48) {
			if (cardNum - 20 >= 0){
				dotNum = cardNum - 20;
			}
		}
		if (cardNum >= 49 && cardNum <= 58) {
			if (cardNum - 50 >= 0){
				dotNum = cardNum - 50;
			}
		}
		if (cardNum >= 59 && cardNum <= 61) {
			if (cardNum - 60 >= 0){
				dotNum = cardNum - 60;
			}
		}
		$('#progressbar ul li').eq(dotNum).css("opacity",0.7);
	}

	function outProgressBar(){
		$('#progressbar ul').animate({top: '-40px'},500,'easeOutExpo',function(){
			setProgressBar();
		});
	}

	function setProgressBar(){

		$('#progressbar ul').empty();
		$('.step' + currentChapter).each(function(){
			$('#progressbar ul').append('<li></li>');
		});
		outProgressDot();
		inProgressBar();
	}

	function inProgressBar(){
		$('#progressbar ul').animate({top: '19px'},500,'easeOutExpo');
	}

	function delProgressBar(){
		$('#progressbar ul').animate({top: '-40px'},500,'easeOutExpo');
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
	var parentImg = new Array();
	parentImg[0] = '<a href="http://kokoro.mhlw.go.jp/" target="_blank" id="kokoro"><img src="assets/img/common/logo-kokoro.png" alt="こころの耳" /></a><a href="http://www.mhlw.go.jp/" target="_blank" id="goverment"><img src="assets/img/common/logo-mhlw.png" alt="厚生労働省" /></a>';
	parentImg[1] = '<img src="assets/img/parent_step1.png" alt="STEP1 仕事について" width="224" height="50" />';
	parentImg[2] = '<img src="assets/img/parent_step2.png" alt="STEP2 最近1ヶ月の状態について" width="224" height="50" />';
	parentImg[3] = '<img src="assets/img/parent_step3.png" alt="STEP3 周りの方々について" width="224" height="50" />';
	parentImg[4] = '<img src="assets/img/parent_step4.png" alt="STEP4 満足度について" width="224" height="50" />';
	parentImg[5] = '<img src="assets/img/parent_title.png" alt="5分でできる職場のストレスチェック" width="214" height="50" />';


	function setResult(choice){
		result[cardNum] = choice;
	}





	// set chapter

	function setChapter(){

		getChapter();

		if (cardNum == 1 || cardNum == 19 || cardNum == 49 || cardNum == 59) {
			if (previousChapter != currentChapter) {
				outProgressBar();
				outParent();
			}
		}
		if (cardNum == 0) {
			delProgressBar();
			outParent();
		}
		if (cardNum == 62) {
			delProgressBar();
			outParent();
		}


		if (previousChapter != currentChapter) {
			if (cardNum != 62) {
				outProgressBar();
			} else {
				delProgressBar();
			}
			outParent();
		} else {
			outProgressDot();
		}

		previousChapter = currentChapter;
	}

	function getChapter(){
		if (cardNum == 0) {
			currentChapter = 0;
		}
		if (cardNum >= 1 && cardNum <= 18) {
			currentChapter = 1;
		}
		if (cardNum >= 19 && cardNum <= 48) {
			currentChapter = 2;
		}
		if (cardNum >= 49 && cardNum <= 58) {
			currentChapter = 3;
		}
		if (cardNum >= 59 && cardNum <= 61) {
			currentChapter = 4;
		}
		if (cardNum == 62) {
			currentChapter = 5;
		}
	}

});

//メール送信画面
$(function(){
	$('.js_ac, .js_close').on('click', function(){
		var ac_k = $('.js_ac').data('role');
		if(ac_k == 0){
			$('.js_ac').data('role', 1);
			var tT = $('.js_ac').offset().top;
			$('.js_box').slideDown(0, function(){
				$('body, html').animate({scrollTop: tT}, 500);
				$('.js_box .inner').slideDown(300);
			});
		}else{
			$('.js_ac').data('role', 0);
			$('.js_box, .js_box .inner').slideUp(0);
		}
	});
  $('.js_submit').click(function(){
    var mydata = $(this).attr('data-data');
    var email = $('.js_email').val();
		var memo = $('.js_memo').val();
    var data = {email: email, data: mydata, memo: memo};
    $.ajax({
      url: '/u/shokuba-check/reports/send_mail',
      data: data,
      success: function(result){
        if(result != '1'){
        }
      },
      error: function(err){
      }
    });

    $('.js_box').fadeToggle('fast');
    return false;
  });
});
