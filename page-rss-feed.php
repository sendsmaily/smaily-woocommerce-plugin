<?php
require_once '../../../wp-load.php';
if(array_key_exists('token',@$_GET) && rss_token() == @$_GET['token']):
	generateRssFeed();
 else:	
	get_header();
	?>
	<div id="main-content">
		<div class="container">
			<div id="content-area" class="clearfix">
				<div id="left-area">
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<h1 class="main_title"><?php the_title(); ?></h1>
						<div class="entry-content">	
						<h3> Access Denied </h3>
						</div> <!-- .entry-content -->
					</article> <!-- .et_pb_post -->
				</div> <!-- #left-area -->
				<?php get_sidebar(); ?>
			</div> <!-- #content-area -->
		</div> <!-- .container -->
	</div> <!-- #main-content -->
	<?php get_footer(); 
 endif;
 
?>