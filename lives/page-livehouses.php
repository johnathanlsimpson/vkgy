<?php

include('../php/function-render_component.php');

ob_start();
?>
	<template id="template-area">
		<li class="livehouse__area-container"><span class="livehouse__area">{area}</span></li>
	</template>
<?php
$area_template = preg_replace('/'.'<\/?template.*?>'.'/', '', ob_get_clean());

ob_start();
?>
	<template id="template-livehouse">
		<li class="livehouse__livehouse any--flex">
			<span class="livehouse__area"></span>
			<div class="livehouse__name">
				<a href="/lives/&amp;livehouse_id={id}">{name}</a>
				<a class="livehouse__edit any--weaken-color {edit_class}" href="/lives/livehouses/{id}/edit/">Edit</a>
				<div class="any--weaken">{nicknames}</div>
			</div>
		</li>
	</template>
<?php
$livehouse_template = preg_replace('/'.'<\/?template.*?>'.'/', '', ob_get_clean());

$sql_livehouses = '
	SELECT
		lives_livehouses.id,
		lives_livehouses.name,
		lives_livehouses.romaji,
		lives_livehouses.friendly,
		lives_livehouses.capacity,
		COALESCE(areas.name, "?") AS area_name,
		areas.romaji AS area_romaji,
		GROUP_CONCAT(lives_nicknames.nickname) AS nicknames
	FROM lives_livehouses
	LEFT JOIN areas ON areas.id=lives_livehouses.area_id
	LEFT JOIN lives_nicknames ON lives_nicknames.livehouse_id=lives_livehouses.id
	GROUP BY lives_livehouses.id
	ORDER BY
		areas.friendly ASC,
		COALESCE(lives_livehouses.romaji, lives_livehouses.name) ASC';
$stmt_livehouses = $pdo->prepare($sql_livehouses);
$stmt_livehouses->execute();
$rslt_livehouses = $stmt_livehouses->fetchAll();

?>

<style>
	.livehouse__header {
		background: hsl(var(--background--bold));
		font-weight: bold;
		line-height: 1rem;
		margin: -1rem;
		margin-bottom: 0;
		padding: 1rem !important;
		position: -webkit-sticky;
		position: sticky;
		top: 3rem;
		z-index: 2;
	}
	.livehouse__area {
		display: inline-block;
		max-width: 100%;
		min-width: 150px;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		width: 150px;
	}
	.livehouse__area-container {
		border-top-color: hsl(var(--text--secondary));
		font-weight: bold;
		line-height: 1rem;
		margin-top: -1px;
		padding: 0;
		position: -webkit-sticky;
		position: sticky;
		top: calc(6rem - 1px);
		z-index: 1;
	}
	.livehouse__area-container .livehouse__area {
		background: hsl(var(--background));
		margin-left: -1px;
		padding: 1rem 0 1rem 1px;
	}
	@media(max-width:599.99px) {
		.livehouse__header {
			display: none;
		}
		.livehouse__area-container {
			top: 3rem;
		}
		.livehouse__livehouse .livehouse__area {
			min-width: 0;
			width: 0;
		}
		.livehouse__area-container .livehouse__area {
			max-width: none;
			width: 100%;
		}
		.livehouse__edit {
			float: right;
		}
	}
	.livehouse__name {
		flex-grow: 1;
	}
	.livehouse__edit {
		margin-left: 1rem;
	}
</style>

<div class="col c1">
	<h2>
		<?= lang('Livehouse list', 'ライブハウス一覧', 'div'); ?>
	</h2>
	<ul class="livehouse__container text">
		<li class="livehouse__header">
			<span class="livehouse__area">Area</span>
			<span class="livehouse__name">Name</span>
		</li>
		<?php
			foreach($rslt_livehouses as $livehouse) {
				$this_area = $livehouse['area_name'];
				
				if($this_area != $prev_area) {
					echo render_component($area_template, [
						'area' => $livehouse['area_romaji'] ? lang($livehouse['area_romaji'], $livehouse['area_name'], 'parentheses') : $livehouse['area_name'],
					]);
				}
				
				echo render_component($livehouse_template, [
					'id' => $livehouse['id'],
					'edit_class' => $_SESSION['can_add_livehouses'] ? null : 'any--hidden',
					'name' => $livehouse['romaji'] ? lang($livehouse['romaji'], $livehouse['name'], 'parentheses') : $livehouse['name'],
					'nicknames' => $livehouse['friendly'].($livehouse['nicknames'] ? ', '.$livehouse['nicknames'] : null)
				]);
				
				$prev_area = $this_area;
			}
		?>
	</ul>
</div>