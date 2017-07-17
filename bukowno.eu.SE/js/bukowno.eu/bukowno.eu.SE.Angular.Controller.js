var bukownoApp = angular.module("bukownoApp",['ngRoute','ngSanitize','ui.router']);

bukownoApp.config(function($stateProvider) {
	$stateProvider
		.state('uiRouterState', {
			url: ':ID'
		});
});

bukownoApp.controller("bukownoCtrl", function ($scope,$route,$http,$location,$interval,$anchorScroll) {
	$scope.locationChangedFromPage = false;
	$scope.randomImageUrl = "";	
	$scope.randomImageText = modelEmptyRandom.text;
	$scope.random = modelEmptyRandom;
	$scope.pageTitle = "";
	$scope.domainUrl = "http://bukowno.eu/mpw/beskidy.bukowno.eu";
	$scope.currentDirectory = currentDirectory;
	$scope.kafelkiDolne = modelKafelkiDolne;
	$scope.slider = modelSlider;
	$scope.ostatniaaktualizacja = [ { data: currentYear } ];
	
	$scope.emptyGaleria = modelGaleria;
	$scope.galeria = $scope.emptyGaleria;

	$scope.emptyAktualnosci = modelAktualnosci;
	$scope.aktualnosci = $scope.emptyAktualnosci;
	
	$scope.emptyZestawienie = modelZestawienie;
	$scope.zestawienie = $scope.emptyZestawienie;
	$scope.modelTresc = modelTresc[1];
	
	$scope.lata = [ currentYear ];
	
	$scope.currentEkran = "";
	$scope.ajax = { status: null, error: null };
	
	$scope.init = function() {
		$scope.galeria = $scope.emptyGaleria;
		$scope.getOstatniaAktualizacjaJson();
		$scope.showContent('wstepniak',true);
		$scope.getAktualnosciJson();
		$scope.getLataDlaZestawieniaJson();
		$scope.getZestawienieJson();
		$scope.updateGaleryFromHref($location.path());
	}

	$scope.checkCallParameters = function() {
		// sprawdzenie parametrów wywołania				
		// index.html#!/?d=20130822
		if($location.search().hasOwnProperty("d")) {
			//console.log("Parametr wejsciowy = "+$location.search().d);
			$scope.getGaleryFromDate($location.search().d);
			return true;
		}
		return false;
	}
	
	// 	Pobranie JSONa z nazwą galerii dla podanej daty, pobranie odpowiedniej galerii
	$scope.getGaleryFromDate = function(data) {
		var serviceUrl = $scope.domainUrl+"/utils/json.php";
		var url = serviceUrl + "?j=d&d=" + data;
		$scope.getJson(url, 'data');
	}
	
	//  Pobranie JSONa z zawartością folderu galerii
	$scope.getGalleryJson = function() {
		var serviceUrl = $scope.domainUrl+"/index.php";
		var url = serviceUrl + "?j=d&ch=galeria&d=" + $scope.currentDirectory;
		$scope.getJson(url, 'galeria');
		$scope.showContent('galeria',false);
	};
	
	//  Pobranie JSONa z zawartością aktualności
	$scope.getAktualnosciJson = function() {
		var serviceUrl = $scope.domainUrl+"/utils/json.php";
		var url = serviceUrl + "?j=a";
		$scope.getJson(url, 'aktualnosci');
	};

	//  Pobranie JSONa z data ostatniej aktualizacji
	$scope.getOstatniaAktualizacjaJson = function() {
		var serviceUrl = $scope.domainUrl+"/utils/json.php";
		var url = serviceUrl + "?j=o";
		$scope.getJson(url, 'ostatniaaktualizacja');
	};

	//  Pobranie JSONa z zawartością lat dla zestawienia
	$scope.getLataDlaZestawieniaJson = function() {
		var serviceUrl = $scope.domainUrl+"/utils/json.php";
		var url = serviceUrl + "?j=l";
		$scope.getJson(url, 'zestawienie-lata');
	};

	//  Pobranie JSONa z zawartością zestawienia
	$scope.getZestawienieJson = function() {
		var serviceUrl = $scope.domainUrl+"/utils/json.php";
		var url = serviceUrl + "?j=z";
		$scope.getJson(url, 'zestawienie');
	};

	//  Pobranie JSONa z losowym slajdem
	$scope.getRandomJson = function() {
		var serviceUrl = $scope.domainUrl+"/utils/json.php";
		var url = serviceUrl + "?j=r";
		$scope.getJson(url, 'random');
	};

	$scope.getJson = function(url,model) {
		if(model=='galeria') {
			$scope.galeria = $scope.emptyGaleria;					
		}
		$http({
			method : "GET",
			url : url
		}).then(function mySucces(response) {
			if(model=='galeria') {
				$scope.galeria = response.data;
				$scope.pageTitle = $scope.galeria.title;
				modelGalleryItems = null;
				modelGalleryItems = $scope.createGalleryItemsTable($scope.galeria);
			}
			if(model=='aktualnosci') {
				$scope.aktualnosci = response.data;
				$scope.correctDateInAktualnosci();
			}
			if(model=='zestawienie-lata') {
				$scope.lata = response.data;
			}
			if(model=='zestawienie') {
				$scope.zestawienie = response.data;
				modelZestawienie = $scope.zestawienie;
			}
			if(model=='data') {
				var galeria = response.data;
				$scope.currentDirectory = galeria.katalog;
				$scope.getGalleryJson();
			}
			if(model=='ostatniaaktualizacja') {
				$scope.ostatniaaktualizacja = response.data;
			}
			if(model=='random') {
				$scope.random = response.data;
				$scope.randomImageText = $scope.random.text;
				$scope.randomImageUrl = "http://bukowno.eu/mpw/beskidy.bukowno.eu/utils/json.php?j=f&f="+$scope.random.src+"&rand="+Math.random();
			}

			$scope.ajax.status = response.status;
		}, function myError(response) {
			$scope.ajax.status = response.status;
			$scope.ajax.error = response.data;
			if(model=='galeria') {
				$scope.galeria = $scope.emptyGaleria;
			}
			if(model=='aktualnosci') {
				$scope.aktualnosci = $scope.emptyAktualnosci;
				$scope.correctDateInAktualnosci();
			}
			if(model=='zestawienie-lata') {
				$scope.lata = [ currentYear ];
			}
			if(model=='zestawienie') {
				$scope.zestawienie = $scope.emptyZestawienie;						
			}
			if(model=='ostatniaaktualizacja') {
				$scope.ostatniaaktualizacja = [ { data: currentYear } ];
			}
			if(model=='random') {
				$scope.random = modelEmptyRandom;
			}
		});
	}
	
	$scope.updateGalery = function(pos) {
		$scope.currentDirectory = $scope.galeria.foldery[pos].directory+"&r="+$scope.galeria.galeriaDir;
		$scope.getGalleryJson();
		$scope.getAktualnosciJson();				
		$scope.locationChangedFromPage = true;
	}
	
	$scope.updateGaleryFromOkruszki = function(pos) {
		$scope.currentDirectory = $scope.galeria.okruszki[pos].href+"&r="+$scope.galeria.galeriaDir;
		$scope.getGalleryJson();
		$scope.getAktualnosciJson();
		$scope.locationChangedFromPage = true;
	}

	$scope.updateGaleryFromAktualnosci = function(pos) {
		$scope.currentDirectory = $scope.aktualnosci[pos].href;
		$scope.getGalleryJson();
		$scope.getAktualnosciJson();
		$scope.moveToContent();
		$scope.locationChangedFromPage = true;
	}
	
	$scope.updateGaleryFromSlider = function(pos) {
		$scope.currentDirectory = $scope.slider[pos].href;
		$scope.getGalleryJson();
		$scope.getAktualnosciJson();
		$scope.locationChangedFromPage = true;
	}

	$scope.updateGaleryFromKafelkiDolne = function(pos) {
		$scope.currentDirectory = $scope.kafelkiDolne[pos].href;
		$scope.getGalleryJson();
		$scope.getAktualnosciJson();
		$scope.moveToContent();
		$scope.locationChangedFromPage = true;
	}

	$scope.updateGaleryFromMenu = function(href) {
		$scope.currentEkran = 'galeria';
		$scope.currentDirectory = href;
		$scope.getGalleryJson();
		$scope.getAktualnosciJson();
		$scope.locationChangedFromPage = true;
		hideOpenedMenu();		
	}

	$scope.updateGaleryFromModelData = function(dane,mode) {
		$scope.currentEkran = 'galeria';
		if(dane=='tatry') {
			$scope.galeria = modelGaleriaTatry;
		}
		if(dane=='sudety') {
			$scope.galeria = modelGaleriaSudety;
		}
		if(dane=='alpy') {
			$scope.galeria = modelGaleriaAlpy;
		}
		if(dane=='pirenejeapeniny') {
			$scope.galeria = modelGaleriaPirenejeApeniny;
		}
		$scope.pageTitle = $scope.galeria.title;
		modelGalleryItems = null;
		modelGalleryItems = $scope.createGalleryItemsTable($scope.galeria);
		hideOpenedMenu();				
		if(mode) {
			$scope.locationChangedFromPage = mode;
		}
	}

	$scope.updateGaleryFromZestawienie = function(pos) {
		var i = 0;
		var ok = false;
		while (i<$scope.zestawienie.length) {
			if($scope.zestawienie[i].rok == $scope.getCurrentYear()) {
				ok = true;
				break;
			}
			++i;
		}
		if (ok) {
			$scope.currentDirectory = $scope.zestawienie[i].wycieczki[pos].href;
			$scope.getGalleryJson();
			$scope.getAktualnosciJson();			
			$scope.moveToContent();
			$scope.locationChangedFromPage = true;
		}
	}

	$scope.stringBeginsWith = function(needle, haystack) {
		return (haystack.substr(0, needle.length) == needle);
	}
	
	$scope.updateGaleryFromHref = function(href) {		
		if($scope.checkCallParameters()) {
			return;
		}		
		if(href=="/") {
			$scope.showContent("wstepniak",false);
			return;
		}
		
		var checked = false;
		var toCheckArray = ['g','m','o','a','c','z','d','k','s','r','y'];
		
		var mode = '';
		var i;
		for(i=0; i<toCheckArray.length;++i) {
			mode = toCheckArray[i];
			var toCheck = "/mode="+mode+"&state=";
			if($scope.stringBeginsWith(toCheck,href)) {
				href = href.substring(toCheck.length);
				checked = true;
				break;
			}
		}
		if(!checked) {
			return;
		}		
		// wyłączenie okna modalnego pokazu slajdow
		var modalOpened = $('#id_randomWithModal').is(":visible"); 
		if(modalOpened) {
			$('#id_randomWithModal').modal('toggle');
		}
		
		href = href.replace(/~2F/g,'/');
		if(mode=="d") {
			$scope.updateGaleryFromModelData(href,false);
			return;
		}
		if(mode=="c") {
			$scope.showContent(href,false);
			if(href == "random") {
				$('#id_randomInside').show();
			}
			return;
		}
		// w historii powracamy do zakładki z latami w zestawieniu
		if(mode=="r") {
			$scope.showContent("zestawienie",false);
			return;		
		}		 
		// w historii powracamy do zakładki z latami w kalendarzu
		if(mode=="y") {
			$scope.showContent("kalendarz",false);
			return;
		}		 
		
		if(mode=='m') {
			$scope.currentEkran = 'galeria';
		}
		$scope.currentDirectory = href;
		$scope.getGalleryJson();
		$scope.getAktualnosciJson();
		if(mode=='m') {
			hideOpenedMenu();
		}		
		if(mode=='a' || mode=='z' || mode=='k') {
			$scope.moveToContent();
		}
	}
	
	$scope.correctHistory = function (pos,mode) {
		var href = "/";
		if(mode=="g") {
			href = $scope.galeria.foldery[pos].directory+"&r="+$scope.galeria.galeriaDir;
		}
		if(mode=="m" || mode=="d" || mode=="c") {
			href = pos;
		}
		if(mode=="o") {
			href = $scope.galeria.okruszki[pos].href+"&r="+$scope.galeria.galeriaDir;
		}
		if(mode=="a") {
			href = $scope.aktualnosci[pos].href;
		}
		if(mode=="z") {
			var i = 0;
			var ok = false;
			while (i<$scope.zestawienie.length) {
				if($scope.zestawienie[i].rok == $scope.getCurrentYear()) {
					ok = true;
					break;
				}
				++i;
			}
			if(ok) {
				if(pos<$scope.zestawienie[i].wycieczki.length) {					
					href = $scope.zestawienie[i].wycieczki[pos].href;
				}
			}
		}
		if(mode=="s") {
			href = $scope.slider[pos].href;
		}
		if(mode=="k") {
			href = $scope.kafelkiDolne[pos].href;
		}
		return "mode="+mode+"&state="+href;
	}
	
	$scope.withThumb = function(pos) {
		var img = $scope.galeria.foldery[pos].img;
		if (img.indexOf("folder.gif") !=-1) {
			return false;
		}
		return true;
	}

	$scope.prepareFolderDescription = function(pos) {
		var folder = $scope.galeria.foldery[pos];
		var str = folder.opis;
		var b_newLine = false;
		if ( folder.liczba_zdjec.length>0 ) {
			str = str + "<br/>" + folder.liczba_zdjec;
			b_newLine = true;
		}
		if ( folder.liczba_wycieczek.length>0 ) {
			if ( !b_newLine ) {
				str = str + "<br/>";
			}
			str = str + folder.liczba_wycieczek;
		}
		return str;
	}
	
	$scope.createGalleryItemsTable = function(modelTable) {
		var galleryItems = [];
		var i;
		for ( i = 0; i < modelTable.zdjecia.length; ++i ) {
			var zdjecie = modelTable.zdjecia[i];
			galleryItems.push( {
				src: $scope.prepareZdjecieHref(modelTable.url,modelTable.galeriaDir,zdjecie.href),
				title: zdjecie.title,
			});
		}
		return galleryItems;
	}

	$scope.correctDateInAktualnosci = function() {
		var i;
		for ( i = 0; i < $scope.aktualnosci.length; ++i ) {
			$scope.aktualnosci[i].correctedData = $scope.aktualnosci[i].data.replace(/_/g,'');
		}
	}

	$scope.showContent = function(ekran,mode) {
		$scope.currentEkran = ekran;				
		if (ekran=='zestawienie') {
			$scope.pageTitle = 'Zestawienie';
			calculateSizesAfterResize();
		} else
		if (ekran=='kalendarz') {
			$scope.pageTitle = 'Kalendarz';
			calculateSizesAfterResize();
		} else
		if (ekran=='random') {
			$scope.pageTitle = 'Pokaz losowych slajdów';
			$('#id_randomWithModal').modal();
		} else
		if (ekran=='wstepniak') {
			$scope.modelTresc = modelTresc[1];
			$scope.pageTitle = $scope.modelTresc.title;
		} else
		if (ekran=='kontakt') {
			$scope.modelTresc = modelTresc[0];
			$scope.pageTitle = $scope.modelTresc.title;
		} else {
			$scope.pageTitle = $scope.galeria.title;
		}
		hideOpenedMenu();
		if(mode) {
			$scope.locationChangedFromPage = mode;
		}
	}
	
	$scope.getCurrentYear = function() {
		return currentYear;
	}
	
	$scope.getMiesiac = function(miesiac) {
		var miesiace = [ 'Styczeń','Luty','Marzec','Kwiecień','Maj','Czerwiec','Lipiec','Sierpień','Wrzesień','Październik','Listopad','Grudzień' ];
		return miesiace[miesiac-1];
	}
	
	$scope.getDzien = function(dzien) {
		var dni = [ 'Pn','Wt','Śr','Cz','Pt','Sb','N' ];
		return dni[dzien-1];
	}
	
	$scope.getNumerDniaTygodnia = function(rok,miesiac) {
		var d = new Date(rok, miesiac-1);
		var dz = d.getDay();
		if(dz==0) dz=7;
		return dz;
	}

	$scope.getLiczbaDniWMiesiacu = function(rok,miesiac) {
		return new Date(rok, miesiac, 0).getDate();
	}
	
	$scope.getCalendar = function(rok,miesiac){
		var nrTyg = $scope.getNumerDniaTygodnia(rok,miesiac);
		var ret = '';
		if (nrTyg-1>0) {
			ret = ret+'<div class="row kalendarz-row kalendarz-margin">';
		}
		var dzien = 1;
		var i;
		for (i=0; i<nrTyg-1; ++i) {
			ret = ret+'<div class="col-xs-1">&nbsp;</div>';
			++dzien;
		}
		for(i=1; i<=$scope.getLiczbaDniWMiesiacu(rok,miesiac); i++) {
			if(dzien==1) {
				ret = ret+'<div class="row kalendarz-row kalendarz-margin">';
			}			
			var s_i = i;
			if(i<10) s_i = '&nbsp;' + i;
			var href = s_i;
			var kolor = "";
			var opis = $scope.getOpisWycieczki(i,miesiac,rok);
			if(opis!=null) {
				href = '<a href="#c" data-pos="'+opis.pos+'" ng-click="updateGaleryFromZestawienie('+opis.pos+')" title="'+opis.opis+'">'+s_i+'</a>';
				kolor = ' kalendarz-kolor-'+opis.kolor;
			}
			ret = ret+'<div class="col-xs-1 text_color center-block' + kolor + '">'+href+'</div>';
			if(dzien==7) {
				ret = ret+'</div>';
				dzien = 1;
			} else {
				++dzien;
			}
		}
		if(dzien>1) {
			for(i=dzien; i<=7; ++i) {
				ret = ret+'<div class="col-xs-1">&nbsp;</div>';
			}
			ret = ret+'</div>';
		}
		return ret;
	}
	
	$scope.getOpisWycieczki = function(dzien,miesiac,rok) {
		var data = rok+".";
		if(miesiac<10) data = data+"0";
		data = data+miesiac+".";
		if(dzien<10) data = data+"0";
		data = data+dzien;
			
		var pos = 0;
		var ok = false;
		while (pos<modelZestawienie.length) {
			if(modelZestawienie[pos].rok == rok) {
				ok = true;
				break;
			}
			++pos;
		}				
		if (ok) {
			var i = 0;
			var ile = modelZestawienie[pos].wycieczki.length;
			while(i<modelZestawienie[pos].wycieczki.length) {
				var wycieczka = $scope.zestawienie[pos].wycieczki[i];
				if(wycieczka.data==data) {								
					return { href: wycieczka.href, opis: wycieczka.opis, kolor: wycieczka.kolor, pos: i };
				}
				++i;
			}
		}
		return null;
	}

	$scope.setCurrentYear = function(rok) {
		currentYear = rok;
	}

	$scope.prepareZdjecieHref = function(url,galeriaDir,href) {
		var ret;
		if ( galeriaDir.length == 0 ) {
			ret = url+href;
		} else {
			var str = "?g=";
			ret = url+href.replace(str, str+galeriaDir+"/");
		}
		return ret;
	}

	$scope.moveToContent = function() {
		/*
		$location.hash('id_a_content');
		$anchorScroll();
		*/
		$anchorScroll('id_a_content');
	}

	$interval(function() {
		if($scope.currentEkran=='random') {
			$scope.getRandomJson();					
			var modalOpened = $('#id_randomWithModal').is(":visible"); 
			if(modalOpened) {
				$('#id_randomInside').hide();
				var rozmiar = parseInt($("#id_randomContent").css("width"),10);
				rozmiar = rozmiar-2*15;
				$("#id_randomImage_2").css("width",rozmiar);	
				// Wyświetlamy tytuł zdjęcia dla pokazu losowych slajdów						
				$('#id_randomImage_2').imagesLoaded()
					.done( function( instance ) {
						$scope.$apply();
					})
					.fail( function( instance ) {
						$scope.$apply();
					});
			} else {
				$('#id_randomInside').show();
				// Wyświetlamy tytuł zdjęcia dla pokazu losowych slajdów						
				$('#id_randomImage_1').imagesLoaded()
					.done( function( instance ) {
						$scope.$apply();
					})
					.fail( function( instance ) {
						$scope.$apply();
					});						
			}
			calculateSizesAfterResize();
		}
	}, randomImageRefreshTime*1000);
	
	$scope.$watch(function() {
		return $location.path();
	}, function(value) {
		if(!$scope.locationChangedFromPage) {
			if($location.search().hasOwnProperty("d")) {
				value = value + "?" + $location.search().d;
			}
			$scope.updateGaleryFromHref(value);
		}
		$scope.locationChangedFromPage = false;
	});
	
	$scope.init();	
});
