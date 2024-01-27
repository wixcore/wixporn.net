        <?php 

        if (is_ajax()) { 
            return ; 
        }


        $justweb = jw_theme_settings(); 
        ?>
    	</div>
        <!--/ Body site Content -->
        
    </div>
    <!--/ Wrap Site Container -->

    <div class="container footer">
        <div class="copyright">&copy; <?php echo $justweb['copyright']; ?></div>
    </div>

    <div id="ds_alerts"></div>

    <?php ds_foot(); ?>

    <!-- Page Generate: <?php echo get_page_gen(); ?> sec. -->
</body></html>