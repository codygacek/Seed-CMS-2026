		</div>
	</section>

	<footer class="site-footer">
		<div class="fluid-container mx-auto p-6">
			<p class="text-sm">&copy;{{ date('Y') }} {{ get_setting('footer_copyright_company') }}.<br>All rights reserved.</p>
			
			@include(layouts_uri('partials.social-media'))
		</div>
	</div>

	<script src="{{ theme_uri('assets/js/jquery.js') }}"></script>
	<script src="{{ theme_uri('assets/js/jquery.fancybox.min.js') }}"></script>
	<script src="{{ theme_uri('assets/js/swiper.min.js') }}"></script>
	<script src="{{ theme_uri('assets/js/app.js') }}"></script>

</body>
</html>