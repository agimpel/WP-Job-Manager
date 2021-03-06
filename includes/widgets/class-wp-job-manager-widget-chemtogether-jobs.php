<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Chemtogether Jobs widget.
 *
 * @package wp-job-manager
 * @since 1.21.0
 */
class WP_Job_Manager_Widget_Chemtogether_Jobs extends WP_Job_Manager_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wp_post_types;

		// translators: Placeholder %s is the plural label for the job listing post type.
		$this->widget_name        = sprintf( __( 'Chemtogether %s', 'wp-job-manager' ), $wp_post_types['job_listing']->labels->name );
		$this->widget_cssclass    = 'job_manager widget_featured_jobs';
		$this->widget_description = __( 'Display a list of Chemtogether listings on your site.', 'wp-job-manager' );
		$this->widget_id          = 'widget_chemtogether_jobs';
		$this->settings           = array(
			'title'   => array(
				'type'  => 'text',
				// translators: Placeholder %s is the plural label for the job listing post type.
				'std'   => sprintf( __( 'Chemtogether %s', 'wp-job-manager' ), $wp_post_types['job_listing']->labels->name ),
				'label' => __( 'Title', 'wp-job-manager' ),
			),
			'number'  => array(
				'type'  => 'number',
				'step'  => 1,
				'min'   => 1,
				'max'   => '',
				'std'   => 10,
				'label' => __( 'Number of listings to show', 'wp-job-manager' ),
			),
			'orderby' => array(
				'type'    => 'select',
				'std'     => 'date',
				'label'   => __( 'Sort By', 'wp-job-manager' ),
				'options' => array(
					'date'          => __( 'Date', 'wp-job-manager' ),
					'title'         => __( 'Title', 'wp-job-manager' ),
					'author'        => __( 'Author', 'wp-job-manager' ),
					'rand_featured' => __( 'Random', 'wp-job-manager' ),
				),
			),
			'order'   => array(
				'type'    => 'select',
				'std'     => 'DESC',
				'label'   => __( 'Sort Direction', 'wp-job-manager' ),
				'options' => array(
					'ASC'  => __( 'Ascending', 'wp-job-manager' ),
					'DESC' => __( 'Descending', 'wp-job-manager' ),
				),
			),
		);
		parent::__construct();
	}

	/**
	 * Echoes the widget content.
	 *
	 * @see WP_Widget
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		wp_enqueue_style( 'wp-job-manager-job-listings' );

		if ( $this->get_cached_widget( $args ) ) {
			return;
		}

		$instance = array_merge( $this->get_default_instance(), $instance );

		ob_start();

		$title_instance = esc_attr( $instance['title'] );
		$number         = absint( $instance['number'] );
		$orderby        = esc_attr( $instance['orderby'] );
		$order          = esc_attr( $instance['order'] );
		$title          = apply_filters( 'widget_title', $title_instance, $instance, $this->id_base );
		$jobs           = get_job_listings(
			array(
				'posts_per_page' => $number,
				'orderby'        => $orderby,
				'order'          => $order,
				'meta_query' => array(
					array(
						'key'   => '_spotlight',
						'value' => 1,
					),
				),
				'tax_query' => array(
					array(
						'taxonomy' => 'job_listing_region',
						'field'    => 'slug',
						'terms'    => 'chemtogether',
						'operator' => 'IN',
					),
				),
			)
		);

		if ( $jobs->have_posts() ) : ?>

			<?php echo $args['before_widget']; // WPCS: XSS ok. ?>

			<?php
			if ( $title ) {
				echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // WPCS: XSS ok.
			}
			?>

			<ul class="job_listings">

				<?php
				while ( $jobs->have_posts() ) :
					$jobs->the_post();
					?>

					<?php get_job_manager_template_part( 'content-widget', 'job_listing' ); ?>

				<?php endwhile; ?>

			</ul>
			<br>
			<div style="text-align:center;">
			<a href="/job-region/chemtogether/" class="button button--size-small" style="margin:auto;">View all offers at Chemtogether</a>
			</div>

			<?php echo $args['after_widget']; // WPCS: XSS ok. ?>

		<?php else : ?>

			<?php get_job_manager_template_part( 'content-widget', 'no-jobs-found' ); ?>

		<?php
		endif;

		wp_reset_postdata();

		$content = ob_get_clean();

		echo $content; // WPCS: XSS ok.

		$this->cache_widget( $args, $content );
	}
}

register_widget( 'WP_Job_Manager_Widget_Chemtogether_Jobs' );
