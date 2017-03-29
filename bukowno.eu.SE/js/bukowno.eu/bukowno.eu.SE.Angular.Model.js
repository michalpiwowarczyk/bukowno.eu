var mustRender = false;
var galleryPluginCreated = false;
var currentYear = new Date().getFullYear();
var isGalleryPackery = false;
var $gridGalleryPackery = null;

var modelBackgrounds = [];
var countBackgrounds = 1000;
var randomImageRefreshTime = 5;

var modelKafelkiDolne = [
	{ class: "list-group-item-info", href: "galeria", h: "Góry", text: "Galeria związana z górami małymi i mniejszymi: Beskidy, Karpaty, Sudety, Tatry, Alpy, Pireneje, Apeniny" },
	{ class: "list-group-item-success", href: "galeria/CiekaweMiejsca&r=../bukowno.eu.SE", h: "Ciekawe miejsca", text: "Galeria związana z wyjazdami: Ardeny, Alzacja, Włochy, Hiszpania, Aragonia" },
	{ class: "list-group-item-warning", href: "galeria/Malamut&r=../bukowno.eu.SE", h: "Malamut", text: "Co nieco o piesku, jak sobie chodził i mieszkał" },
	{ class: "list-group-item-danger", href: "galeria/PoGodzinach&r=../bukowno.eu.SE", h: "Po godzinach", text: "Troche hobby i innych pierdoł w wolnych chwilach" },
];

var modelSlider = [
	{ img: "images/slider/image012.jpg", alt: "Góry", size: "h2", h: "Góry", text: "Galeria o spacerkach po górach", href: "galeria", button: "Przejdź" },
	{ img: "images/slider/image009.jpg", alt: "Góry", size: "h2", h: "Karpaty Centralne", text: "Mała Vatra, Vel'ka Fatra, Tatry, Nizke Tatry, Chocske vrchy ...", href: "galeria/51_4_CentralneKarpatyZachodnie", button: "Przejdź" },
	{ img: "images/slider/image010.jpg", alt: "Góry", size: "h2", h: "Tatry Wysokie", text: "Polskie i Słowackie", href: "galeria/51_4_CentralneKarpatyZachodnie/514_5_TatryWysokie", button: "Przejdź" },
	{ img: "images/slider/image011.jpg", alt: "Góry", size: "h2", h: "Tatry Zachodnie", text: "Polskie i Słowackie", href: "galeria/51_4_CentralneKarpatyZachodnie/514_5_TatryZachodnie", button: "Przejdź" },
	{ img: "images/slider/image015.jpg", alt: "Góry", size: "h4", h: "Centralne Alpy Wschodnie", text: "Alpy Otztalskie, Zillertalskie, Sztubaje, Ratikon, Samnaun, Sesvenna, Wysokie Taury", href: "galeria/43_4_CentralneAlpyWschodnie", button: "Przejdź" },
	{ img: "images/slider/image014.jpg", alt: "Góry", size: "h4", h: "Północne Alpy Wapienne", text: "Alpy Lechtalskie, Sarntalskie, Verwall, Wettersteingebirge, Allgauer", href: "galeria/43_4_PolnocneAlpyWapienne", button: "Przejdź" },
	{ img: "images/slider/image013.jpg", alt: "Góry", size: "h4", h: "Południowe Alpy Wapienne", text: "Alpy Julijskie, Kamnickie, Livigno, Adamello, Dolomity, Brenta, Karawanki, Grupa Ortlera", href: "galeria/43_6_PoludnioweAlpyWapienne", button: "Przejdź" },
	{ img: "images/slider/image002.jpg", alt: "Góry", size: "h3", h: "Alpy Zachodnie", text: "Alpy Graickie, Walijskie, Vanoise", href: "galeria/43_AlpyZachodnie", button: "Przejdź" },
	{ img: "images/slider/image004.jpg", alt: "Góry", size: "h2", h: "Pireneje", text: "Maladeta, Andora", href: "galeria/61_1_Pireneje", button: "Przejdź" },
	{ img: "images/slider/image003.jpg", alt: "Góry", size: "h2", h: "Apeniny", text: "Corno Grande", href: "galeria/64_ApeninySrodkowe", button: "Przejdź" },
	{ img: "images/slider/image017.jpg", alt: "Góry", size: "h2", h: "Modele", text: "Teciaki Pezetki i inne", href: "galeria/PoGodzinach/Modele&r=../bukowno.eu.SE", button: "Przejdź" },
	{ img: "images/slider/image016.jpg", alt: "Góry", size: "h3", h: "Duster", text: "Wyciszenie, Car Audio, Sypialnia :)", href: "galeria/PoGodzinach/Dracula&r=../bukowno.eu.SE", button: "Przejdź" },
	{ img: "images/slider/image001.jpg", alt: "Góry", size: "h2", h: "Malamut", text: "O Naszym starym malamucie", href: "galeria/Malamut&r=../bukowno.eu.SE", button: "Przejdź" },
	{ img: "images/slider/image005.jpg", alt: "Góry", size: "h3", h: "Ardeny", text: "Ardeny, Linia Maginotta, Linia Zygfryda", href: "galeria/CiekaweMiejsca/Ardeny&r=../bukowno.eu.SE", button: "Przejdź" },
	{ img: "images/slider/image006.jpg", alt: "Góry", size: "h4", h: "Włochy 2015", text: "Polskie cmentarze wojskowe we Włoszech, Rzym, Wenecja, Wezuwiusz, Florencja, Piza, Pompeje", href: "galeria/CiekaweMiejsca/Wlochy&r=../bukowno.eu.SE", button: "Przejdź" },
	{ img: "images/slider/image007.jpg", alt: "Góry", size: "h2", h: "Francja, Luksemburg, Belgia 2014", text: "Alzacja, Benelux", href: "galeria/CiekaweMiejsca/Alzacja&r=../bukowno.eu.SE", button: "Przejdź" },
	{ img: "images/slider/image008.jpg", alt: "Góry", size: "h2", h: "Hiszpania 2015", text: "Katalonia, Aragonia", href: "galeria/CiekaweMiejsca/Hiszpania&r=../bukowno.eu.SE", button: "Przejdź" },
];			

var modelGaleriaTatry = { 
	url: "http://bukowno.eu/mpw/beskidy.bukowno.eu/index.php", directory: "galeria/51_4_CentralneKarpatyZachodnie", title: "Tatry", opis: "Tatry Wysokie, Tatry Zachodnie, Nizke Tatry",
	foldery: [
		{ directory: "galeria/51_4_CentralneKarpatyZachodnie/514_5_TatryWysokie", img: "../images/folder.gif", opis: "514.53 Tatry Wysokie", liczba_zdjec: 0, liczba_wycieczek: 0 },
		{ directory: "galeria/51_4_CentralneKarpatyZachodnie/514_5_TatryZachodnie", img: "../images/folder.gif", opis: "514.52 Tatry Zachodnie", liczba_zdjec: 0, liczba_wycieczek: 0 },
		{ directory: "galeria/51_4_CentralneKarpatyZachodnie/514_9_NizkeTatry", img: "../images/folder.gif", opis: "514.91 Nizke Tatry", liczba_zdjec: 0, liczba_wycieczek: 0 }
	], zdjecia: [], 
	okruszki: [
		{ href: "galeria", text: "Góry" },
		{ href: "galeria/51_4_CentralneKarpatyZachodnie", text: "51.4 Centralne Karpaty Zachodnie" }
	]};

	var modelGaleriaSudety = { 
	url: "http://bukowno.eu/mpw/beskidy.bukowno.eu/index.php", directory: "galeria", title: "Sudety", opis: "Sudety Zachodnie, Sudety Środkowe, Sudety Wschodnie",
	foldery: [
		{ directory: "galeria/332_3_SudetyZachodnie", img: "../images/folder.gif", opis: "332.3 Sudety Zachodnie", liczba_zdjec: 0, liczba_wycieczek: 0 },
		{ directory: "galeria/332_4-5_SudetySrodkowe", img: "../images/folder.gif", opis: "332.4-5 Sudety Środkowe", liczba_zdjec: 0, liczba_wycieczek: 0 },
		{ directory: "galeria/332_6_SudetyWschodnie", img: "../images/folder.gif", opis: "332.6 Sudety Wschodnie", liczba_zdjec: 0, liczba_wycieczek: 0 }
	], zdjecia: [], 
	okruszki: [
		{ href: "galeria", text: "Góry" }
	]};
	
var modelGaleriaAlpy = { 
	url: "http://bukowno.eu/mpw/beskidy.bukowno.eu/index.php", directory: "galeria", title: "Alpy", opis: "Centralne Alpy Wschodnie, Północne Alpy Wapienne, Południowe Alpy Wapienne, Alpy Zachodnie",
	foldery: [
		{ directory: "galeria/43_4_CentralneAlpyWschodnie", img: "../images/folder.gif", opis: "43.4 Centralne Alpy Wschodnie", liczba_zdjec: 0, liczba_wycieczek: 0 },
		{ directory: "galeria/43_4_PolnocneAlpyWapienne", img: "../images/folder.gif", opis: "43.4 Północne Alpy Wapienne", liczba_zdjec: 0, liczba_wycieczek: 0 },
		{ directory: "galeria/43_6_PoludnioweAlpyWapienne", img: "../images/folder.gif", opis: "43.6 Południowe Alpy Wapienne", liczba_zdjec: 0, liczba_wycieczek: 0 },
		{ directory: "galeria/43_AlpyZachodnie", img: "../images/folder.gif", opis: "43 Alpy Zachodnie", liczba_zdjec: 0, liczba_wycieczek: 0 },
	], zdjecia: [], 
	okruszki: [
		{ href: "galeria", text: "Góry" }
	]};

var modelGaleriaPirenejeApeniny = { 
	url: "http://bukowno.eu/mpw/beskidy.bukowno.eu/index.php", directory: "galeria", title: "Pireneje, Apeniny", opis: "Pireneje, Apeniny",
	foldery: [
		{ directory: "galeria/61_1_Pireneje", img: "../images/folder.gif", opis: "611 Pireneje", liczba_zdjec: 0, liczba_wycieczek: 0 },
		{ directory: "galeria/64_ApeninySrodkowe", img: "../images/folder.gif", opis: "64 Apeniny Środkowe", liczba_zdjec: 0, liczba_wycieczek: 0 }
	], zdjecia: [], 
	okruszki: [
		{ href: "galeria", text: "Góry" }
	]};

var modelGaleria = { url: null, directory: null, title: "Loading...", opis: "one moment please, transfer in progress", foldery: [], zdjecia: [], okruszki: [] };
var modelGalleryItems = [];
var modelAktualnosci = {};
var modelZestawienie = [ { rok: null, got: null, dystans: null, podejscie: null, czas: null, wycieczki: [] } ];
var modelEmptyRandom = { src: "", text: "Losowy slajd" };

var modelTresc = [ 
	{ title: "Kontakt", text: "Znajdziesz mnie tutaj gdzie jesteś, albo tutaj:<br/>michal.piwowarczyk@bukowno.eu<br/>michal.piwowarczyk.eu@gmail.com<br/><br/>Fejsa nie używam, nie tłituje, nie lajkuje, strona nie używa cookie. Nie wiem czy spełnia wymagania WCAG, powinna za to być responsywna, a na pewno jest to aplikacja jednostronna SPA.<br/><br/>Wszystkie zdjęcia znajdujące się na tej stronie są własnością autora witryny i nie możesz ich wykorzystywać bez jego zgody.<br/><br/><br/>Poprzednia wersja strony <a href='http://beskidy.bukowno.eu/mpw/beskidy.bukowno.eu' target='_blank' class='text_color'>http://beskidy.bukowno.eu/mpw/beskidy.bukowno.eu</a>" },
	{ title: "Wstępniak", text: "Nie znajdziesz tu tekstów które możesz czytać bądź cytować, nie ma opisów, sprawozdań, nie ma galerii, to jest tylko skromny pamiętnik bo pamięć mam ulotną.<br/><br/>Pozdrawiam<br/>Michał Piwowarczyk" } ];
	
var currentDirectory = "galeria";
