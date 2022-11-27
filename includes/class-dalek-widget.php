<?php

class Dalek_UserList_Widget {
    static $list = [];
    static $usercount = 0;
    static $servicescount = 0;
    static $opercount = 0;
    static $chancount = 0;
	static $helpercount = 0;
}
class Dalek_Widget extends WP_Widget {

    /**
     * Sets up the widgets name etc
     */
    public function __construct() {
        $widget_ops = array( 
            'classname' => 'dalek_widget',
            'description' => 'Display chat counts',
        );
        parent::__construct( 'superdalek', 'Chat', $widget_ops );
    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ) {
		global $wpdb;
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		Dalek_UserList_Widget::$list = $wpdb->get_results("SELECT * FROM dalek_user");
		foreach(Dalek_UserList_Widget::$list as $user)
		{
			if (strpos($user->usermodes,"S") !== false)
			{
				++Dalek_UserList_Widget::$servicescount;
				continue;
			}
			if (strpos($user->usermodes,"o") !== false)
			{
				++Dalek_UserList_Widget::$opercount;
			}
			if (strpos($user->usermodes,"h") !== false)
			{
				++Dalek_UserList_Widget::$helpercount;
			}
			++Dalek_UserList_Widget::$usercount;
		}
		?>
		Online right now:<br>
		<?php echo Dalek_UserList_Widget::$usercount; ?> users.<br>
		<?php echo Dalek_UserList_Widget::$opercount; ?> opers.<br>
		<?php echo Dalek_UserList_Widget::$helpercount; ?> supporters available for help.
		<?php
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'New title', 'dalek_domain' );
		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'dalek_domain' ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';

		return $instance;
	}

}
