##############################################################################
# Copyright notice
#
# (c) 2010 Christian Technology Ministries International Inc.
# All rights reserved
#
# This file is part of the Web-Empowered Church (WEC)
# (http://WebEmpoweredChurch.org) ministry of Christian Technology Ministries
# International (http://CTMIinc.org). The WEC is developing TYPO3-based
# (http://typo3.org) free software for churches around the world. Our desire
# is to use the Internet to help offer new life through Jesus Christ. Please
# see http://WebEmpoweredChurch.org/Jesus.
#
# You can redistribute this file and/or modify it under the terms of the
# GNU General Public License as published by the Free Software Foundation;
# either version 2 of the License, or (at your option) any later version.
#
# The GNU General Public License can be found at
# http://www.gnu.org/copyleft/gpl.html.
#
# This file is distributed in the hope that it will be useful for ministry,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# This copyright notice MUST APPEAR in all copies of the file!
##############################################################################

##############################################################
# This is TypoScript used to modify the core templates to
# display this skin. Rewrite the header, footer, pre code and
# post code libraries and more when needed to change structure
##############################################################

preCodeHeader = HTML
preCodeHeader.value = <div id="bodyWrap">

postCodeHeader = COA
postCodeHeader {
	10 = HTML
	10.value = <div id="pageWrap">

	20 = COA
	20 {
		stdWrap.required = 1
		stdWrap.wrap = <div id="login">|</div>

		10 = COA
		10 {
			10 = TEXT
			# @todo Remove hardcoded label
			10.value = My Account:&nbsp;&nbsp;
			10.if.isTrue = {$loginPID}

			20 = TEXT
			20 {
				# Only show the login link if there's a valid page to link to
				if.isTrue = {$loginPID}
				if.isTrue.insertData = 1

				# @todo Remove hardcoded label
				value = Sign In
				typolink.parameter = {$loginPID}
				typolink.additionalParams = &return_url={getIndpEnv : REQUEST_URI}
				typolink.additionalParams.insertData = 1
			}

			30 = TEXT
			30 {
				# Only show the login link if there's a valid page to link to
				if.isTrue = {$loginPID}
				if.isTrue.insertData = 1

				value = &nbsp;&#124;&nbsp;
			}

			40 = TEXT
			40 {
				# Only show the registration link if there's a valid page to link to
				if.isTrue = {$registerPID}
				if.isTrue.insertData = 1

				# @todo Remove hardcoded label
				value = Sign Up
				typolink.parameter = {$registerPID}
				typolink.additionalParams = &tx_srfeuserregister_pi1[cmd]=create
			}
		}
	}
}

[loginUser = *]
postCodeHeader.20.10 >
postCodeHeader.20.10 = COA_INT
postCodeHeader.20.10 {
	20 = TEXT
	20 {
		data = TSFE:fe_user|user|first_name // TSFE:fe_user|user|username
		# @todo Remove hardcoded label
		wrap = Welcome,&nbsp; | &nbsp;&#124;&nbsp;

		# Only show the edit link if there's a valid page to link to
		typolink.if.isTrue = {$registerPID}
		typolink.if.isTrue.insertData = 1
		typolink.parameter = {$registerPID}
		typolink.additionalParams = &tx_srfeuserregister_pi1[cmd]=edit
	}

	30 = TEXT
	30 {
		# @todo Remove hardcoded label
		value = Sign Out
		typolink.parameter.data = TSFE : id
		typolink.addQueryString = 1
		typolink.addQueryString.method = GET
 		typolink.additionalParams = &logintype=logout
	}
}
[global]



preCodeFeature >


preCodeFeature = HTML
preCodeFeature.value = <div id="contentWrap">
postCodeFeature >

preCodeContent >

preCodeGeneratedContent-1 >
postCodeGeneratedContent-1 >

preCodeContentBlock-1 >
postCodeContentBlock-1 >

preCodeContentBlock-2 >
postCodeContentBlock-2 >

preCodeContentBlock-3 >
postCodeContentBlock-3 >

preCodeGeneratedContent-2 >
postCodeGeneratedContent-2 >

preCodeFooter >
postCodeFooter = HTML
postCodeFooter.value (

	</div>
	<!-- end #pageWrap  --></div>
)



### Lets table classes be added in the RTE
lib.parseFunc_RTE.externalBlocks.table.stdWrap.HTMLparser.tags.table.fixAttrib.class.list >
lib.parseFunc_RTE.nonTypoTagStdWrap.encapsLines.addAttributes.P.class >

globalMenu >
globalMenu = HMENU
globalMenu.entryLevel = 0
globalMenu.excludeUidList = 2
globalMenu.wrap = <ul id="globalMenu">|</ul><!-- end #globalMenu  -->
globalMenu.1 = TMENU
globalMenu.1 {
	noBlur = 1
	NO.wrapItemAndSub = <li>|</li>
	ACT = 1
	ACT.wrapItemAndSub = <li class="active">|</li>
	CUR = 1
	CUR.wrapItemAndSub = <li class="current">|</li>
}

globalMenu.2 = TMENU
globalMenu.2 {
	noBlur = 1
	wrap = <ul>|</ul>
	NO.wrapItemAndSub = <li>|</li>
	ACT = 1
	ACT.wrapItemAndSub = <li class="active">|</li>
	CUR = 1
	CUR.wrapItemAndSub = <li class="current">|</li>
}

globalMenu.3 = TMENU
globalMenu.3 {
	noBlur = 1
	wrap = <ul>|</ul>
	NO.wrapItemAndSub = <li>|</li>
	ACT = 1
	ACT.wrapItemAndSub = <li class="active">|</li>
	CUR = 1
	CUR.wrapItemAndSub = <li class="current">|</li>
}

globalMenu.4 = TMENU
globalMenu.4 {
	noBlur = 1
	wrap = <ul>|</ul>
	NO.wrapItemAndSub = <li>|</li>
	ACT = 1
	ACT.wrapItemAndSub = <li class="active">|</li>
	CUR = 1
	CUR.wrapItemAndSub = <li class="current">|</li>
}


header >
header = COA
header.wrap = <div id="header"> | </div>


# Add the masthead for site title / logo.
header.20 = COA
header.20 {
	wrap = <div id="masthead"> | </div>

	# Add <h1> wrapped title if there's no logo
	10 = TEXT
	10.if.isFalse = {$siteLogo}
	10.value = {$siteTitle}
	10.htmlSpecialChars = 1
	10.typolink.parameter = {$siteURL}
	10.wrap = <h1> | </h1>

	# Add the logo image if one is avaialble.
	20 = IMAGE
	20.if.isTrue = {$siteLogo}
	20.file = {$siteLogo}
	20.file.maxW = 300
	20.file.maxH = 250
	20.alttext.cObject = TEXT
	20.alttext.cObject.value = {$siteTitle}
	20.alttext.cObject.insertData = 1
	20.if.isTrue = {$siteLogo}
	20.stdWrap.typolink.parameter = {$siteURL}
}

header.40 < globalMenu

### Top Navigation
header.50 = COA

### Search box
header.50.10 = COA
header.50.10 {
	# Only show the search box if there is a valid search page.
	if.isTrue = {$searchPID}
	if.isTrue.insertData = 1

	wrap = <div id="search"> | </form></div>

	10 = TEXT
	10 {
		typolink.parameter = {$searchPID}
		typolink.returnLast = url
		wrap = <form id="siteSearch" name="site_search" method="post" action="|">
	}

	# @todo Remove hardcoded label
	20 = HTML
	20.value (
		<label class="outOfSight" for="siteSearchInput">Search the Site</label>
		<input id="siteSearchInput" type="text" value="Search the Site" name="tx_indexedsearch[sword]"/>
		<input id="siteSearchSubmit" type="image" class="searchsubmit" src="{$templavoila_framework.skinPath}css/images/search.png" value="search" name="siteSearchSubmit" />
	)
	20.insertData = 1
}


clientRave = COA
clientRave {
	10 = HTML
	10.value = <div id="clientRave">

	20 < plugin.tx_jmquote_pi1
	20.contentWrap >

	30 = HTML
	30.value = </div>
}

header.60 < clientRave

shareLinks = COA
shareLinks {
	10 = HTML
	10.value = <div id="shareLinks">

	20 < plugin.tx_stsocialnetwork_pi1

	30 = HTML
	30.value = </div>
}

header.55 < shareLinks

footer >
footer = COA
footer {
	wrap = <div id="footer" class="clear"> | </div>
	10 = TEXT
	10.data = date:U
	10.strftime = %Y
	10.wrap = <p id="footerCopyright">&copy;&nbsp; | &nbsp;{$copyright}, {$contact}</p>

	20 = TEXT
	# @todo Remove hardcoded label
	20.value = We are a Web-Empowered Church.
	20.typolink.parameter = http://www.webempoweredchurch.org/
	20.typolink.ATagParams = id="footerHomeLink"
	20.if.isTrue = {$enableWECFooter}
}


additonalDocHeadCode = HTML
additonalDocHeadCode.value (

	<!--[if IE 6]>
		<link rel="stylesheet" type="text/css" href="{$templavoila_framework.skinPath}css/ie6.css" />
	<![endif]-->
)

# "Menu of subpages to these pages (with abstract)"
tt_content.menu.20.4 {
	special = directory
	wrap >
	1 >
	1 = TMENU
	1 {
		target = {$PAGE_TARGET}
		wrap = <div class="sectionMenuWrapper"><div class="sectionMenu">|</div><div class="clearOnly"></div></div>
		NO {
			allWrap = <div class="menuItem"> | </div>
			before.cObject = COA
			before.cObject {
				10 = IMAGE
				10.if.isTrue.field = media
				10.file.import = uploads/media/
				10.file.import.field = media
				10.file.import.listNum = 0
				10.file.width = 108m
				10.alttext.field = title
				10.params = align="left"
				10.stdWrap.typolink.parameter.field = uid
			}

			after.cObject = COA
			after.cObject {
				30 = TEXT
				30.field = abstract
				30.wrap = <p>|</p></div>
			}

			stdWrap.override.field = subtitle
			linkWrap = <div class="wrapper"><h3>|</h3>
			noBlur = 1
		}
	}
}
