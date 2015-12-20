<?php
auth_reauthenticate();

$version_id = gpc_get_int( 'version_id' );
$version = version_get( $version_id );

access_ensure_project_level( config_get( 'manage_project_threshold' ), $version->project_id );

html_page_top();
print_manage_menu( 'manage_proj_ver_edit_page.php' ); ?>
   <div id="manage-proj-version-update-div" class="form-container">
      <form id="manage-proj-version-update-form" method="post" action="<?php echo plugin_page( 'manage_versions_set_do' ) . '&version_id=' . $version_id ?>">
         <fieldset>
            <legend><span><?php echo lang_get( 'edit_project_version_title' ) ?></span></legend>
            <?php echo form_security_field( plugin_page( 'manage_versions_set_do' ) ) ?>
            <input type="hidden" name="version_id" value="<?php echo string_attribute( $version->id ) ?>" />
            <div class="field-container">
               <label for="proj-version-new-version"><span><?php echo lang_get( 'version' ) ?></span></label>
               <span class="input"><input type="text" id="proj-version-new-version" name="new_version" size="32" maxlength="64" value="<?php echo string_attribute( $version->version ) ?>" /></span>
               <span class="label-style"></span>
            </div>
            <div class="field-container">
               <label for="proj-version-date-order"><span><?php echo lang_get( 'date_order' ) ?></span></label>
               <span class="input"><input type="text" id="proj-version-date-order" name="date_order" class="datetime" size="32" value="<?php echo (date_is_null( $version->date_order ) ? '' : string_attribute( date( config_get( 'calendar_date_format' ), $version->date_order ) ) ) ?>" /></span>
               <span class="label-style"></span>
            </div>
            <div class="field-container">
               <label for="proj-version-description"><span><?php echo lang_get( 'description' ) ?></span></label>
               <span class="textarea"><textarea id="proj-version-description" name="description" cols="60" rows="5"><?php echo string_attribute( $version->description ) ?></textarea></span>
               <span class="label-style"></span>
            </div>
            <div class="field-container">
               <label for="proj-version-released"><span><?php echo lang_get( 'released' ) ?></span></label>
               <span class="checkbox"><input type="checkbox" id="proj-version-released" name="released" <?php check_checked( (boolean)$version->released, VERSION_RELEASED ); ?> /></span>
               <span class="label-style"></span>
            </div>
            <div class="field-container">
               <label for="proj-version-obsolete"><span><?php echo lang_get( 'obsolete' ) ?></span></label>
               <span class="checkbox"><input type="checkbox" id="proj-version-obsolete" name="obsolete" <?php check_checked( (boolean)$version->obsolete, true ); ?> /></span>
               <span class="label-style"></span>
            </div>

            <?php event_signal( 'EVENT_MANAGE_VERSION_UPDATE_FORM', array( $version->id ) ); ?>
            <span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'update_version_button' ) ?>" /></span>
         </fieldset>
      </form>
   </div>
<?php
html_page_bottom();