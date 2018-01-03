<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.2.5 - Licence Number VBF6025393
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2017 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| #        www.vbulletin.com | www.vbulletin.com/license.html        # ||
|| #################################################################### ||
\*======================================================================*/

if (!isset($GLOBALS['vbulletin']->db))
{
	exit;
}

print_form_header('index', 'home');
print_table_header($vbphrase['vbulletin_developers_and_contributors']);
print_column_style_code(array('white-space: nowrap', ''));
print_label_row('<b>' . $vbphrase['software_developed_by'] . '</b>', '
	<a href="https://www.vbulletin.com/" target="vbulletin">vBulletin Solutions Inc.</a>,
	<a href="https://www.internetbrands.com/" target="vbulletin">Internet Brands, Inc.</a>,
', '', 'top', NULL, false);

print_label_row('<b>' . $vbphrase['product_manager'] . '</b>', '
	Paul Marsden
', '', 'top', NULL, false);

print_label_row('<b>' . $vbphrase['business_product_development'] . '</b>', '
	John McGanty,
	Marjo Mercado
', '', 'top', NULL, false);

print_label_row('<b>' . $vbphrase['engineering'] . '</b>', '
	David Grove,
	Jin-Soo Jo,
	Kevin Sours,
	Paul Marsden
', '', 'top', NULL, false);

print_label_row('<b>' . $vbphrase['qa'] . '</b>', '
	Sebastiano Vassellatti,
	Yves Rigaud
', '', 'top', NULL, false);

print_label_row('<b>' . $vbphrase['support'] . '</b>', '
	Aakif Nazir,
	Christine Tran,
	Dominic Schlatter,
	Joe DiBiasi,
	Joshua Gonzales,
	Lynne Sands,
	Mark Bowland,
	Trevor Hannant,
	Wayne Luke
', '', 'top', NULL, false);

print_label_row('<b>' . $vbphrase['special_thanks'] . '</b>', '
	Adrian Harris,
	Alan Chiu,
	Alan Orduno,
	Allen Lin,
	Andreas Kirbach,
	Andrew Elkins,
	Andrew Vo,
	Andy Huang,
	Carrie Anderson,
	Chen Avinadav,
	Chris Holland,
	Colin Frei,
	D\'Marco Brown,
	Danco Dimovski,
	Darren Gordon,
	Daniel Lee,
	Danny Morlett,
	Don Kuramura,
	Edwin Brown,
	Eric Johney,
	Freddie Bingham,
	Fabian Schonholz,
	Fei Leung,
	Fernando Varesi,
	Floris Fiedeldij Dop,
	Gary Carroll,
	George Liu,
	Glenn Vergara,
	Hartmut Voss,
	Jake Bunce,
	Jasper Aguila,
	Jay Quiambao,
	Jen Rundell,
	Jerry Hutchings,
	Joe Rosenblum,
	John Percival,
	Jorge Tiznado,
	Kay Alley,
	Kevin Connery,
	Kier Darby,
	Kyle Furlong,
	Lawrence Cole,
	Mark Jean,
	Meghan Sensenbach,
	Mert Gokceimam,
	Michael Anders,
	Michael Biddle,
	Michael Henretty,
	Michael \'Mystics\' K&ouml;nig,
	Michael Lavaveshkul,
	Miguel Montaño,
	Mike Sullivan,
	Neal Sainani,
	Olga Mandrosov,
	Omid Majdi,
	Prince Shah,
	Pritesh Shah,
	Reenan Arbitrario,
	Rene Jimenez,
	Riasat Al Jamil,
	Scott MacVicar,
	Scott Molinari,
	Sophie Xie,
	Stephan \'pogo\' Pogodalla,
	Xiaoyu Huang,
	Yasser Hamde,
	Zachery Woods,
	Zoltan Szalay,
	Zuzanna Grande
', '', 'top', NULL, false);

print_label_row('<b>' . $vbphrase['contributions_from'] . '</b>', '
	Ace Shattock,
	Adrian Sacchi,
	Ahmed,
	Ajinkya Apte,
	Alex Matthews,
	Ali Madkour,
	Anders Pettersson,
	Aston Jay,
	Billy Golightly,
	bjornstrom,
	Bob Pankala,
	Brad Wright,
	Brett Morriss,
	Brian Swearingen,
	Brian Gunter,
	Chevy Revata,
	Christian Hoffmann,
	Christopher Riley,
	Daniel Clements,
	David Bonilla,
	David Webb,
	David Yancy,
	Dody,
	digitalpoint,
	Don T. Romrell,
	Doron Rosenberg,
	Duane Piosca,
	Elmer Hernandez,
	Emon Khan,
	Enrique Pascalin,
	Eric Sizemore,
	Fernando Munoz,
	Hanson Wong,
	Harry Scanlan,
	Gavin Robert Clarke,
	Geoff Carew,
	Giovanni Martinez,
	Green Cat,
	Hanafi Jamil,
	Hani Saad,
	Ivan Anfimov,
	Ivan Milanez,
	Jacquii Cooke,
	Jan Allan Zischke,
	Jaume L&oacute;pez,
	Jelle Van Loo,
	Jeremy Dentel,
	Joan Gauna,
	Joanna W.H.,
	Joe Velez,
	Joel Young,
	John Jakubowski,
	John Yao,
	Jonathan Javier Coletta,
	Joseph DeTomaso,
	Justin Turner,
	Kevin Schumacher,
	Kevin Wilkinson,
	Kira Lerner,
	Kolby Bothe,
	Kym Farnik,
	Lamonda Steele,
	Lisa Swift,
	Marco Mamdouh Fahem,
	Mark Hennyey,
	Mark James,
	Marlena Machol,
	Martin Meredith,
	Maurice De Stefano,
	Matthew Gordon,
	Merjawy,
	Michael Fara,
	Michael Kellogg,
	Michael Mendoza,
	Michael Miller,
	Michael Perez,
	Michael Pierce,
	Michlerish,
	Milad Kawas Cale,
	miner,
	Nathan Wingate,
	nickadeemus2002,
	Ole Vik,
	Oscar Ulloa,
	Overgrow,
	Peggy Lynn Gurney,
	Priyanka Porwal,
	Pieter Verhaeghe,
	Refael Iliaguyev,
	Reshmi Rajesh,
	Ricki Kean,
	Rob (Boofo) Hindal,
	Robert Beavan White,
	Roms,
	Ruth Navaneetha,
	Ryan Ashbrook,
	Ryan Royal,
	Sal Colascione III,
	Scott William,
	Scott Zachow,
	Shawn Vowell,
	Stefano Acerbetti,
	Steve Machol,
	Sven "cellarius" Keller,
	Tariq Bafageer,
	The Vegan Forum,
	ThorstenA,
	Tom Murphy,
	Tony Phoenix,
	Torstein H&oslash;nsi,
	Troy Roberts,
	Tully Rankin,
	Vinayak Gupta
	', '', 'top', NULL, false);
print_label_row('<b>' . $vbphrase['copyright_enforcement_by'] . '</b>', '
	<a href="https://www.vbulletin.com/" target="vbulletin">vBulletin Solutions Inc.</a>
', '', 'top', NULL, false);
print_table_footer();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92745 $
|| # $Date: 2017-02-03 07:39:48 -0800 (Fri, 03 Feb 2017) $
|| ####################################################################
\*======================================================================*/
?>
