<style>
body {
  background: #989898;
  color: #494949 !important;
  font-family: Arial, Calibri, Verdana, Geneva, sans-serif !important;
  font-size: 12px !important;
  line-height: 1.2 !important;
}
.container {
    width: 940px;
}
a {
  color: #494949;
  text-decoration: none;
}
a:hover {
  text-decoration: underline;
}
a.highlight_link {
  text-decoration: underline;
}
a.highlight_link:hover {
  text-decoration: none;
}
.themeHeaderContainer {
    background: #989898;
}
#header {
  zoom: 1;
  margin: 0 0 10px;
}
#header:before,
#header:after {
  content: "\0020";
  display: block;
  font-size: 0;
  height: 0;
  visibility: hidden;
}
#header:after {
  clear: both;
}
#header #logo {
  margin: 0;
  padding: 25px 0 0;
}
#header #logo h1 {
  margin: 0 0 12px;
}
#header #logo p {
  color: #fff;
  font-size: 14px;
}
#header nav {
  float: right;
  font-size: 15px;
  font-weight: bold;
  line-height: 41px;
  text-transform: uppercase;
}
#header nav li {
  background: url('https://declaration.declarvins.net/images/fonds/bg_nav_header.png') 0 0 repeat-x;
  border: 1px solid #fff;
  display: inline;
  float: left;
  margin: 0 0 0 18px;
}
#header nav li a {
  color: #494949;
  float: left;
  padding: 0 10px;
}
#header nav li a:hover {
  text-decoration: none;
}
#header nav li.profil {
  background: #86005b;
  border-color: #5f0141;
}
#header nav li.profil a {
  color: #fff;
}
#header nav li.backend {
  background: #c582af;
  border-color: #a55d8d;
}
#header nav li.backend a {
  color: #86005b;
}
#header p.deconnexion {
  font-size: 13px;
  font-style: italic;
  text-align: right;
}
#header p.deconnexion a {
  color: #fff;
  text-decoration: underline;
}
#header p.deconnexion a:hover {
  text-decoration: none;
}
#header p.deconnexion a.backend_link {
  color: #494949;
}

#barre_navigation {
  background: #f1f1f1;
  border-bottom: 1px solid #e1e1e0;
  padding: 4px 4px 0;
  margin: 0 -12px;
  zoom: 1;
}
#barre_navigation:before,
#barre_navigation:after {
  content: "\0020";
  display: block;
  font-size: 0;
  height: 0;
  visibility: hidden;
}
#barre_navigation:after {
  clear: both;
}
#barre_navigation a:hover {
  text-decoration: none;
}
#barre_navigation #nav_principale {
  float: left;
  line-height: 35px;
  font-size: 14px;
  margin: 0 0 -10px;
  top: -10px;
  position: relative;
  text-align: center;
  list-style: none;
  padding-left: 0;
}
#barre_navigation #nav_principale li {
  float: left;
  margin: 0px;
}
#barre_navigation #nav_principale li a {
  color: #494949;
  float: left;
  padding: 5px 12px 0 16px;
}
#barre_navigation #nav_principale li.actif {
  font-weight: bold;
}
#barre_navigation #nav_principale li.actif a,
#barre_navigation #nav_principale li a:hover,
#barre_navigation #nav_principale li a:focus {
  background: url('https://declaration.declarvins.net/images/fonds/bg_nav_principale.png') left 0 no-repeat;
  color: #fff;
}
#barre_navigation #nav_infociel {
  float: right;
  line-height: 35px;
  font-size: 14px;
  margin: 0 0 -10px;
  top: -10px;
  position: relative;
  text-align: center;
  list-style: none;
}
#barre_navigation #nav_infociel li {
  float: left;
  margin: 0 4px 0 0;
}
#barre_navigation #nav_infociel li span {
  color: #494949;
  float: left;
  padding: 5px 15px 0 18px;
  cursor: pointer;
}
#barre_navigation #nav_infociel li .ciel_connect {
  background: url('https://declaration.declarvins.net/images/pictos/pi_connect.png') left 14px no-repeat;
}
#barre_navigation #nav_infociel li .ciel_disconnect {
  background: url('https://declaration.declarvins.net/images/pictos/pi_disconnect.png') left 14px no-repeat;
}
#barre_navigation #nav_infociel li .ciel_help {
  background: url('https://declaration.declarvins.net/images/pictos/pi_help.png') left 17px no-repeat;
}


#sous_barre_navigation {
  background: #fff;
  padding: 4px 4px 0;
  margin: 0px -12px;
  zoom: 1;
}
#sous_barre_navigation:before,
#sous_barre_navigation:after {
  content: "\0020";
  display: block;
  font-size: 0;
  height: 0;
  visibility: hidden;
}
#sous_barre_navigation:after {
  clear: both;
}
#sous_barre_navigation a:hover {
  text-decoration: none;
}
#sous_barre_navigation #actions_etablissement {
  float: right;
  line-height: 27px;
}
#sous_barre_navigation #actions_etablissement li {
  display: inline;
  float: left;
}
#sous_barre_navigation #actions_etablissement li.backend {
  background: #c582af;
  border: 1px solid #a55d8d;
  height: 25px;
  line-height: 25px;
  padding: 0 10px;
}
#sous_barre_navigation #actions_etablissement li.backend a {
  color: #86005b;
}
#sous_barre_navigation #actions_etablissement li.etablissement_courant a {
  background: url('https://declaration.declarvins.net/images/boutons/btn_etablissement_courant.png') right 0 no-repeat;
  padding: 0 20px 0 0;
}
#sous_barre_navigation #actions_etablissement li.etablissement_courant a span {
  background: url('https://declaration.declarvins.net/images/boutons/btn_etablissement_courant.png') 0 0 no-repeat;
  float: left;
  padding: 0 0 0 27px;
}
#sous_barre_navigation #actions_etablissement li.quitter {
  margin-right: -10px;
}
#sous_barre_navigation #actions_etablissement li a {
  color: #fff;
  float: left;
}
#sous_barre_navigation #actions_etablissement li img {
  display: block;
}
#sous_barre_navigation #actions_etablissement .popup_form {
  position: relative;
  right: 3px;
  top: 3px;
}
#sous_barre_navigation #actions_etablissement .popup_form button,
#sous_barre_navigation #actions_etablissement .popup_form input {
  margin-top: 2px;
}

#footer {
  background: #f1f1f1 url('https://declaration.declarvins.net/images/fonds/bg_footer.png') 0 0 repeat-x;
  padding: 12px 20px;
  margin: 0 -12px;
  zoom: 1;
}
#footer:before,
#footer:after {
  content: "\0020";
  display: block;
  font-size: 0;
  height: 0;
  visibility: hidden;
}
#footer:after {
  clear: both;
}
#footer .copyright {
  float: left;
}
#footer .copyright p {
  color: #737373;
  font-size: 12px;
  margin: 0 0 10px;
}
#footer .copyright p a {
  color: #737373;
}
#footer ul#logos_footer {
  float: right;
  list-style: none;
}
#footer ul#logos_footer li {
  float: left;
  margin: 0 0 0 15px;
}

h1 {
  color: #86005b;
  font-size: 22px;
  font-weight: normal;
  margin: 0 0 20px;
}
.step-item {
    background: none;
    border: 1px solid #86005b;
}
.step-item.active {
  background-color: #86005b;
}
.step-item.active a, a:link, a:visited, .link {
  color: #fff;
}
.step-item::before {
  border: 1px solid #86005b;
  color: #86005b;
}
.step-item.active::before {
  border: 1px solid #fff;
  color: #fff;
}
a, a:link, a:visited, .link {
    color: #86005b;
}
a:hover, a:active {
    color: #c582af;
    text-decoration: none;
}
.btn-danger {
    color: #fff !important;
}
.nav-pills > li.active > a, .nav-pills > li.active > a:hover, .nav-pills > li.active > a:focus {
	background-color: #86005b;
}
.btn-primary, .btn-primary:hover, .btn-primary:focus, .btn-primary.focus, .btn-primary:active, .btn-primary.active, .open > .dropdown-toggle.btn-primary {
	background-color: #86005b;
	border-color: #86005b;
}

</style>
