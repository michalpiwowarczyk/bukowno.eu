function ShowProfile(dir,name) {
	window.open(ProfileSRC(dir,name),"TrackProfile","height=420 width=1060 resizable=yes scrollbars=yes");
}

function ShowProfile_Zestawienie(dir,name) {
	document.getElementById("profil_img").src=ProfileSRC(dir,name);
	window.open(ProfileSRC(dir,name),"TrackProfile","height=420 width=1060 resizable=yes scrollbars=yes");
}

function ProfileSRC(dir,name) {
	var s_tmp = "";
	for (var i=0; i<dir; ++i) 
		s_tmp = s_tmp + "../";
	return s_tmp+"utils/phpgpx/index.php?name="+name;
}


function showLayer(x) {
	with(document.getElementById(x).style) {
		display=display=='none'?'block':'none';
	}
}

function showLayer_Zestawienie(x,name) {
	with(document.getElementById(x).style) {
		display=display=='none'?'block':'none';
	}
	document.getElementById("profil_img").src=ProfileSRC(1,name);
}

function ProfilDIV(dir,gpx) {
	return '<div id="profil" style="display:none;position:absolute;left:5%;" width="96%"><img id="profil_img" src="'+ProfileSRC(dir,gpx)+'" /></div>';
}


function ProfilHREF(dir,gpx) {
	return ", <a href='javascript:ShowProfile("+dir+",\""+gpx+"\")' onmouseover=\"showLayer('profil')\" onmouseout=\"showLayer('profil')\"><b style=\"color:green;\">Profil</b></a>";
}
