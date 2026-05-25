	</div><!-- /#main-content .site-content -->

	<footer class="site-footer" role="contentinfo">
		<div class="site-footer__main">
			<div class="container">
				<?php get_template_part( 'template-parts/footer/footer-columns' ); ?>
			</div>
		</div>

		<div class="footer-hud">
			<div class="container">
				<div class="footer-hud__inner">
					<span class="label-mono">© <?php echo esc_html( date( 'Y' ) ); ?> MONDO SEGNALETICA SOC. COOP. · LUCCA · ITALY</span>
					<span class="footer-hud__legal label-mono">
						<a href="<?php echo esc_url( get_privacy_policy_url() ); ?>" style="color:inherit; text-decoration:none;">Privacy Policy</a>
						<span style="opacity:0.4"> · </span>
						<a href="<?php echo esc_url( home_url( '/cookie-policy' ) ); ?>" style="color:inherit; text-decoration:none;">Cookie Policy</a>
					</span>
				</div>
			</div>
		</div>
	</footer>

</div><!-- /#page .site -->

<?php wp_footer(); ?>
</body>
</html>
