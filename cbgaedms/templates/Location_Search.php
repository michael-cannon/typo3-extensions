<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<?php include_once('scripts/include.php'); ?>
<div id="EDMS">
<div>
	<h1>Locations</h1>
	<h2 class="locationsearch" title="Location Search">search locations</h2>
	<div class="clearer"></div>
	<div id="LocationSearch">
	<h3>Locations</h3>
	<div class="clearer"></div>
	<div id="LocationSearchWrap">
		<div class="btm">
		<div class="clearer"></div>
		<form id="edmsdt" name="edmsdt" method="post" action="<?php $this->printAsLink(); ?>">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td class="label">Name:</td>
					<td><?php $this->printAsLocationsSuggest('agency', $this->get('agency')); ?></td>
				</tr>
				<tr>
					<td class="label">City:</td>
					<td><?php $this->printAsLocationCitiesSuggest('city', $this->get('city')); ?></td>
				</tr>
				<tr>
					<td class="label">State/Province:</td>
					<td><?php $this->printAsLocationStatesSuggest('state', $this->get('state')); ?></td>
				</tr>
				<tr>
					<td class="label">Country:</td>
					<td><?php $this->printAsLocationCountriesSuggest('country', $this->get('country')); ?></td>
				</tr>
				<tr>
					<td class="label">Silo:</td>
					<td><?php $this->printAsLocationSilosSuggest('agencysilo', $this->get('agencysilo')); ?></td>
				</tr>
				<tr>
					<td class="label"></td>
					<td>
					<input type="submit" value="Location_List" id="Search"
						name="tx_cbgaedms[action]"/>
					<input type="submit" value="Location_Search" id="Reset"
						name="tx_cbgaedms[action]"/>
					</td>
				</tr>
			</table>
		</form>
		</div>
	</div>
	</div>
	<div class="clearer"></div>
</div>
