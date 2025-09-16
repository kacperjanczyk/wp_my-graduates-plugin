<table class="form-table my-graduates-table">
    <tr>
        <th><label for="graduate_first_name"><?php _e('First Name', 'my-graduates'); ?> <span class="required">*</span></label></th>
        <td>
            <input type="text" id="graduate_first_name" name="graduate_first_name"
                   value="<?php echo esc_attr($firstName); ?>" class="regular-text" required />
        </td>
    </tr>
    <tr>
        <th><label for="graduate_last_name"><?php _e('Last Name', 'my-graduates'); ?> <span class="required">*</span></label></th>
        <td>
            <input type="text" id="graduate_last_name" name="graduate_last_name"
                   value="<?php echo esc_attr($lastName); ?>" class="regular-text" required />
        </td>
    </tr>
    <tr>
        <th><label for="graduate_description"><?php _e('Description', 'my-graduates'); ?></label></th>
        <td>
                    <textarea id="graduate_description" name="graduate_description" rows="5"
                              class="large-text"><?php echo esc_textarea($description); ?></textarea>
        </td>
    </tr>
    <tr>
        <th><label for="graduate_photo"><?php _e('Photo', 'my-graduates'); ?></label></th>
        <td>
            <input type="hidden" id="graduate_photo" name="graduate_photo"
                   value="<?php echo esc_url($photo); ?>" />
            <button type="button" class="button js-select-media">
                <?php _e('Select Media', 'my-graduates'); ?>
            </button>
            <div id="photo-preview" class="media-preview">
                <?php if ($photo): ?>
                    <img src="<?php echo esc_url($photo); ?>"
                         alt="<?php _e('Graduate Photo', 'my-graduates'); ?>" />
                    <br><button type="button" class="button-link js-remove-media">
                        <?php _e('Remove Media', 'my-graduates'); ?>
                    </button>
                <?php endif; ?>
            </div>
        </td>
    </tr>
</table>
