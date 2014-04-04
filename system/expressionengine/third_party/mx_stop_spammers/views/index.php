		<div id="filterMenu">
			<fieldset>
				<legend><?=lang('filtered_members').': '.$total_rows.' / '.$total?></legend>

			<?=form_open($_form_base."", array('name'=>'filterform', 'id'=>'filterform'), '')?>
				<div class="group">
						
					<label for="member_group"><?=lang('member_group').NBS.NBS?></label><?=form_dropdown('member_group', $member_groups, $member_group_selected, 'id="f_member_group"').NBS.NBS?>
					<label for="perpage"><?=lang('perpage').NBS.NBS?></label><?=form_dropdown('perpage_selected', $perpage, $perpage_selected, 'id="f_perpage"').NBS.NBS?>
				</div> 
				<div class="group">
				<?=form_checkbox('have_bio', 'yes', $have_bio, 'id="have_bio"')?> <label for="have_bio"><?=lang('have_bio').NBS.NBS?></label>
				<?=form_checkbox('links_in_bio', 'yes', $links_in_bio, 'id="links_in_bio"')?> <label for="links_in_bio"><?=lang('links_in_bio').NBS.NBS?></label>
				<?=form_checkbox('num_in_username', 'yes', $num_in_username, 'id="num_in_username"')?> <label for="num_in_username"><?=lang('num_in_username').NBS.NBS?></label>	
				<?=form_checkbox('have_signature', 'yes', $have_signature, 'id="have_signature"')?> <label for="have_signature"><?=lang('have_signature').NBS.NBS?></label>
				<?=form_checkbox('no_last_visit', 'yes', $no_last_visit, 'id="no_last_visit"')?> <label for="no_last_visit"><?=lang('no_last_visit').NBS.NBS?></label>
				</div> 
				<div class="group">
				<?=form_checkbox('trusted', 'yes', $trusted, 'id="trusted"')?> <label for="trusted"><?=lang('trusted').NBS.NBS?></label>
				<?=form_checkbox('have_comments', 'yes', $have_comments, 'id="have_comments"')?> <label for="have_comments"><?=lang('have_comments').NBS.NBS?></label>
				<?php if($forum_flag) : ?> <?=form_checkbox('have_topics', 'yes', $have_topics, 'id="have_topics"') ?> <label for="have_topics"><?=lang('have_topics').NBS.NBS?></label> 	<?php endif; ?>
				</div> 
				
				<div>

					<?=form_submit('submit', lang('search'), 'class="submit" id="search_button"')?>
				</div>

			<?=form_close()?>
			</fieldset>
			<!-- filterMenu -->
	
			
<?=form_open($_form_base.$_search_url, array('name' => 'target', 'id' => 'target'), '')?>
		<?php
			
			$this->table->set_template($cp_pad_table_template);

			$heading = array(
				'<a id="expand_bio" style="text-decoration: none;" href="#">+/-</a>',
				lang('username'),
				lang('url'),
				lang('last_visit'),
				lang('join_date'),
				lang('group'),
				lang('spamlist'),
				lang('blocked'),
				'<a id="toggle" style="text-decoration: none;" href="#">+/-</a>'
			);

			$this->table->set_heading($heading);
			
			if (count($members) > 0)
			{
				foreach ($members  as $member)
				{
					$row = array(
						array('data' => (($member->bio != '') ? '<img src="/themes/cp_themes/default/images/field_collapse.png" alt="expand">' : '').'<span class="member_id">'. $member->member_id.'</span>', 'class' => 'ch-t '.(($member->bio != '') ? 'additional' : '').(($member->trusted == 'y') ? 'trusted' : '')),
						array('data' => '<a href="'.BASE.'&C=myaccount&id='.$member->member_id.'"><span class="username">'. $member->username.'</span></a> | <a href="http://www.google.ru/search?q='.$member->username.'+expressionengine" target="_blank">google</a><br>(<span class="email">'.$member->email.'</span>)<br><span class="ip_address">'.$member->ip_address.'</span> '.(($member->total_comments > 0 ) ? lang('has_comments') : '').((isset($member->total_forum_posts)) ?  (($member->total_forum_posts > 0 ) ? lang('has_topics') : '') : ''), 'class' => ''),
						array('data' => '<a href="'. $member->url.'">'. substr($member->url,0, 30) .'</a>', 'class' => ' url'),
						array('data' => (($member->last_visit == 0) ? ' - ' : $this->localize->human_time($member->last_visit)), 'class' => ''),
						array('data' => $this->localize->format_date('%Y', $member->join_date).'-'.$this->localize->format_date('%m', $member->join_date).'-'.$this->localize->format_date('%d', $member->join_date), 'class' => ''),
						array('data' => $member->group_title, 'class' => ''),
						array('data' => '<span class="spamlist-ch"><input  value="'.lang('check').'" class="submit" type="button"></span>', 'class' => ' '),
						array('data' => '<span class="block-ch"><input  value="'.lang('toban').'" class="submit" type="button"></span>', 'class' => ''),
						array('data' => '<input class="ttoggle" name="toggle[]" value="'. $member->member_id.'" type="checkbox"></td>', 'class' => 'chbox')		
						
					);
 


					$this->table->add_row($row);
					if ($member->bio) {
						$row = array(
							array('data' => $member->bio, 'colspan' => '10')
						);
					
						$this->table->add_row($row );
					}
					
				}
			}
			
			echo $this->table->generate();
			?>  



<div class="tableFooter">
	<div class="tableSubmit">
				<?=form_submit('submit', lang('submit'), 'class="submit" id="mbr_action"').NBS.NBS?>
				<?=form_dropdown('action', $form_options, '', 'id="members_action"').NBS.NBS?>
	</div>

	<span class="pagination" id="filter_pagination"><?=$pagination?></span>
</div>	
		
		<?=form_close()?>
		

		
<script>
url_base =  "<?= str_replace("&amp;", "&",$cp_url)?>";
no_results =  "<?= lang('no_results')?>";
its_spam =  "<?= lang('its_spam')?>";
please_wait = "<?= lang('please_wait')?>";
str_repeat = "<?= lang('str_repeat')?>";

</script>

<div id='loading'><?= lang('please_wait')?></div>