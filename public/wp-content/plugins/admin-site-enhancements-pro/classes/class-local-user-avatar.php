<?php

namespace ASENHA\Classes;

/**
 * Class for Local User Avatar module
 *
 * @since 6.9.5
 */
class Local_User_Avatar {

    /**
     * Render user avatar fields
     * 
     * @link https://plugins.trac.wordpress.org/browser/simple-user-avatar/tags/4.3/admin/class-sua-admin.php
     * @since 6.2.0
     */
    public function render_personal_profile_fields__premium_only( $user ) {
        
        // Get user meta
        $attachment_id = get_user_meta( $user->ID, 'local_user_avatar_attachment_id', true );

        ?>
        <table class="form-table">
            <tbody>
                <tr id="local-user-avatar">
                    <th scope="row">
                        <label for="btn-media-add">Profile Picture</label>
                    </th>
                    <td>
                        <?php echo get_avatar( $user->ID, 96, '', $user->display_name, [ 'class' => 'asenha-attachment-avatar' ] ); ?>
                        <p class="description <?php if ( ! empty( $attachment_id)  ) echo 'hidden'; ?>" id="asenha-attachment-description">You're seeing the default profile picture.</p>
                        <div class="asenha-btn-container">
                            <button type="button" class="button" id="btn-media-add">Change</button>
                            <button type="button" class="button <?php if ( empty( $attachment_id) ) echo 'hidden'; ?>" id="btn-media-remove">Reset to Default</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Hidden attachment ID -->
        <input type="hidden" name="local_user_avatar_attachment_id" value="<?php echo esc_attr( $attachment_id ); ?>" />
        <?php
    }
    
    /**
     * Update user meta upon saving changes when editing user profile
     * 
     * @link https://plugins.trac.wordpress.org/browser/simple-user-avatar/tags/4.3/admin/class-sua-admin.php
     * @since 6.2.0
     */
    public function update_personal_profile_user_meta__premium_only( $user_id ) {

        // If user don't have permissions
        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }

        // Delete old user meta
        delete_user_meta( $user_id, 'local_user_avatar_attachment_id' );

        // Validate POST data and, if is ok, add it
        if ( isset( $_POST['local_user_avatar_attachment_id'] ) && is_numeric( $_POST['local_user_avatar_attachment_id'] ) ) {
            add_user_meta( $user_id, 'local_user_avatar_attachment_id', (int) $_POST['local_user_avatar_attachment_id'] );
        }

        return true;
        
    }
    
    /**
     * 
     * @link https://plugins.trac.wordpress.org/browser/simple-user-avatar/tags/4.3/admin/class-sua-admin.php
     * @since 6.2.0
     */
    public function delete_personal_profile_user_meta__premium_only( $post_id ) {
        global $wpdb;
        
        $wpdb->delete(
            $wpdb->usermeta,
            array(
                'meta_key'      => 'local_user_avatar_attachment_id',
                'meta_value'    => (int) $post_id,
            ),
            array(
                '%s',
                '%d'
            )
        );
    }
    
    /**
     * Override avatar with the local one already uploaded
     * 
     * @link https://plugins.trac.wordpress.org/browser/simple-user-avatar/tags/4.3/public/class-sua-public.php
     * @since 6.2.0
     */
    public function override_avatar_with_local__premium_only( $avatar, $id_or_email, $size, $default, $alt ) {

        // // Get user ID, if is numeric
        // if ( is_numeric( $id_or_email ) ) {

        //  $user_id = (int) $id_or_email;

        // // If is string, maybe the user email
        // } elseif ( is_string( $id_or_email ) ) {

        //  // Find user by email
        //  $user = get_user_by( 'email', $id_or_email );

        //  // If user doesn't exists or this is not an ID
        //  if ( ! isset( $user->ID ) || ! is_numeric( $user->ID ) ) {
        //      return $avatar;
        //  }

        //  $user_id = (int) $user->ID;

        // // If is an object
        // } elseif ( is_object( $id_or_email ) ) {

        //  // If is an ID
        //  if ( isset( $id_or_email->ID ) && is_numeric( $id_or_email->ID ) ) {
        //    $user_id = (int)$id_or_email->ID;
        //  // If this is an Comment Object
        //  } elseif ( isset( $id_or_email->comment_author_email ) ) {
        //    $user = get_user_by( 'email', $id_or_email->comment_author_email );

        //    // If user doesn't exists or this is not an ID
        //    if ( ! isset( $user->ID ) || ! is_numeric( $user->ID ) ) {
        //      return $avatar;
        //    }

        //    $user_id = (int) $user->ID;
        //  } else {
        //      return $avatar;
        //  }
        // }
        
        $user_id = $this->get_user_id_from_idoremail( $id_or_email );
        
        if ( ! is_numeric( $user_id ) ) {
            return $avatar;
        }

        // Get attachment ID from user meta
        $attachment_id = get_user_meta( $user_id, 'local_user_avatar_attachment_id', true );
        if ( empty( $attachment_id ) || ! is_numeric( $attachment_id ) ) {
            return $avatar;
        }

        // Get attachment image src
        $attachment_src = wp_get_attachment_image_src( $attachment_id, 'medium' );

        // Override WordPress src
        if ( $attachment_src !== false ) {
            $avatar = preg_replace( '/src=("|\').*?("|\')/', "src='{$attachment_src[0]}'", $avatar );
        }

        // Get attachment image srcset
        $attachment_srcset = wp_get_attachment_image_srcset( $attachment_id );

        // Override WordPress srcset
        if( $attachment_srcset !== false ) {
            $avatar = preg_replace( '/srcset=("|\').*?("|\')/', "srcset='{$attachment_srcset}'", $avatar );
        }

        return $avatar;
    }
    
    /**
     * Override avatar URL with the local one
     * 
     * @since 6.4.1
     */
    public function override_avatar_url_with_local__premium_only( $url, $id_or_email, $args ) {

        $user_id = $this->get_user_id_from_idoremail( $id_or_email );
        
        if ( ! is_numeric( $user_id ) ) {
            return $url;
        }

        // Get attachment ID from user meta
        $attachment_id = get_user_meta( $user_id, 'local_user_avatar_attachment_id', true );

        if ( empty( $attachment_id ) || ! is_numeric( $attachment_id ) ) {
            return $url;
        }

        // Get attachment image src
        $attachment_src = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );

        if ( is_array( $attachment_src ) && isset( $attachment_src[0] ) ) {
            return $attachment_src[0]; // the thumbnail URL
        } else {
            return $url;    
        }

    }

    /**
     * Get user ID from $id_or_email
     * 
     * @since 6.4.1
     */
    public function get_user_id_from_idoremail( $id_or_email ) {
        
        $user_id = false;

        // Get user ID, if is numeric
        if ( is_numeric( $id_or_email ) ) {

            $user_id = (int) $id_or_email;

        // If is string, maybe the user email
        } elseif ( is_string( $id_or_email ) ) {

            // Find user by email
            $user = get_user_by( 'email', $id_or_email );

            if ( is_object( $user ) ) {
                if ( property_exists( $user, 'ID' ) || is_numeric( $user->ID ) ) {
                    $user_id = (int) $user->ID;
                }               
            }

        // If is an object
        } elseif ( is_object( $id_or_email ) ) {

            // If is an ID
            if ( property_exists( $id_or_email, 'ID' ) && is_numeric( $id_or_email->ID ) ) {
              $user_id = (int)$id_or_email->ID;
            // If this is a Comment Object
            } elseif ( property_exists( $id_or_email, 'comment_author_email' ) ) {
              $user = get_user_by( 'email', $id_or_email->comment_author_email );

              if ( is_object( $user ) ) {
                  if ( property_exists( $user, 'ID' ) || is_numeric( $user->ID ) ) {
                      $user_id = (int) $user->ID;
                  }             
              }

            } else {}

        }
        
        if ( is_numeric( $user_id ) ) {
            return $user_id;
        } else {
            return false;
        }

    }
    
}