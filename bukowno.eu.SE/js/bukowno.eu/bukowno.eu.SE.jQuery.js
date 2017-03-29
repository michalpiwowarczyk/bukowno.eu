function calculateSizesAfterResize() {
	//console.log('Resizing elements');
	// Ustalenie rozmiarów kontenera zdjęć tła <body>
	divMCW = $("#id_main_container").css("width");
	divMCH = $("#id_main_container").css("height");
	$("#id_image_container").css("width",divMCW);
	$("#id_image_container").css("height",divMCH);
	$("#id_image_container").data("backstretch").resize();		

	// Ustalenie rozmiarów kontenera zdjęć dla Swipera
	x2 = parseInt($(".row").css("width"),10);
	if (x2>720) {
		x2=720;
	}
	x = x2*10/12;
	y = x*0.75;
	
	$(".swiper-container").css("width",x);
	$(".swiper-container").css("height",y);
			
	$(".swiper-wrapper").css("width",x);
	$(".swiper-wrapper").css("height",y);

	$(".swiper-slide").css("width",x);
	$(".swiper-slide").css("height",y);

	$(".swiper-container-inside").css("width",x);
	$(".swiper-container-inside").css("height",y);

	$(".swiper_image").css("width",x);
	$(".swiper_image").css("height",y);
	
	$(".swiper_img_sizes").css("width",x);
	$(".swiper_img_sizes").css("height",y);

	if ( !galleryPluginCreated ) {
		if( $("#id_galeria").length<=0 ) {
			$.magnificPopup.instance.items = [];
		}
	}

	// Układamy kafelki folderow
	if ( $("#id_foldery").length>0 ) {	
		$('#id_foldery').imagesLoaded()
			.always( function( instance ) {
				$('#id_foldery').equalize({reset: true});
			}
		);
	}
	
	// Konfigurujemy pokaz slajdow
	if ( $("#id_galeria").length>0 ) {
		if ( $gridGalleryPackery!=null ) {
			$gridGalleryPackery.packery('destroy');
			$gridGalleryPackery = null;
			isGalleryPackery = false;
		}
		
		// Układamy kafelki galerii
		if(!isGalleryPackery && $gridGalleryPackery==null ) {
			var ileKafelkow=0;
			$('#id_galeria > div > a').each(function(){
				++ileKafelkow;
			});
			if(ileKafelkow>0) {
				$('#id_galeria').imagesLoaded()
					.always( function( instance ) {
						$gridGalleryPackery = $("#id_galeria").packery({			
							itemSelector: '.grid-item',
							transitionDuration: '5s',
						});			
					}
				);
				isGalleryPackery = true;
			}			
		}
		
		if ( !galleryPluginCreated || 1>0 ) {
			var z=0;
			$('#id_galeria > div > a').each(function(){
				var $this = $(this);
				var index = $this.data('index');
				if (isNaN(index)) {
					index = z;
				}
				++ z;
				$this.magnificPopup({ 
					key: 'my-popup', 
					items: modelGalleryItems,
					index: index,
					type: 'image',
					tLoading: 'Loading image #%curr%...',
					mainClass: 'mfp-img-mobile',
					gallery: {
						enabled: true,
						navigateByImgClick: true,
						preload: [1,1]
					},
					image: {
						Error: 'The image could not be loaded.',
						titleSrc: function(item) {
							return item.el.attr('title');
						}
					}
				});
			});
		} else {
			//console.log("odtwarzam liste pozycji dla pokazu w galerii, liczba pozycji "+modelGalleryItems.length);
			$.magnificPopup.instance.items = modelGalleryItems;
		}
		galleryPluginCreated = true;		
	}
}

function hideOpenedMenu() {
	$('#id_menu_up').removeClass("in");
	$('#id_menu_up').attr("aria-expanded","false");				
}

$(document).ready(function(){
	// inicjalizacja tabeli z obrazkami tła
	var i;
	for(i=0;i<countBackgrounds;++i) {
		modelBackgrounds.push("http://bukowno.eu/mpw/beskidy.bukowno.eu/utils/json.php?j=b&rand="+Math.random());
	}
	
	// Zmiana prędkości wysuwania elementów menu poziomego górnego
    $(".dropdown-toggle").click( function(){
			$(this).siblings('ul').css("background-color","#f8f8f8");
			if ( $(this).parent().hasClass("open") ) {
				$('.dropdown-menu').hide();
				$(this).siblings('ul').slideDown('slow');
			} else {
				if ( $("body").width() >= 748 ) {
					$(this).siblings('ul').show();
					$(this).siblings('ul').slideUp('slow');
				}
			}
	});

	// Dopasowanie rozmiaru tekstu
	arrayRM = [ "#id_content_text", "#id_banery_item1", "#id_banery_item2", "#id_banery_item3", "#id_banery_item4" ];
	for ( i=0; i< arrayRM.length; ++i ) {
		element = arrayRM[i];
		$(element).responsiveMeasure({
			idealLineLength: 66,
			minimumFontSize: 16,
			maximumFontSize: 300,
			ratio: 4/3,
		});
	}

	// Kafelki dolne
	$('#id_banery').packery({
		itemSelector: '.grid-item',
		gutter: 0,
		transitionDuration: '1s',
	});

	// Kafelki aktualności
	$('#id_aktualnosci').packery({
		itemSelector: '.grid-item',
		gutter: 5,
		transitionDuration: '2s',
	});

	$("#id_image_container").backstretch( modelBackgrounds,	{	
		fade: 1000,
		preload: 1,
		transition: 'fade',
		transitionDuration: 15000,
		animateFirst: true, 
		alwaysTestWindowResolution: true,
		alignX: 1.0,
	});

	// Konfiguracja pokazu slajdów
	var mySwiper = new Swiper ('.swiper-container', {
		loop: true,
		pagination: '.swiper-pagination',
		nextButton: '.swiper-button-next',
        prevButton: '.swiper-button-prev',
        paginationClickable: true,
		direction: 'horizontal',
		spaceBetween: 0,
		slidesPerView: 1,
		centeredSlides: true,
		effect: 'slide',
		mousewheelControl: true,
		autoplay: 10000,
		speed: 500,
        autoplayDisableOnInteraction: false,
		// hashnav: true,
		observer: true,
		observeParents: true,
    });

	// Obsługa zmiany rozmiaru okna dla obrazka w tle
	$('#id_main_container').elementResize(function(event) {
		calculateSizesAfterResize();
	});	
	
	calculateSizesAfterResize();

	$(document).on('show.bs.tab', function(e) {
		var target = $(e.target);
		currentYear = target.data('rok');
		//console.log("Changing currentYear to "+currentYear);
	});	
});
