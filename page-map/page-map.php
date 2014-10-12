<?php
/*
Template Name: Map
*/
?><?php
get_header("map");?>

<div id="" class="">

    <div style="padding: 20px;">
        <div id="mymap1"></div>
    </div>

	<?php
		$points = '';
        $post_titles = '';
        $post_as = '';

		query_posts('posts_per_page=1000');

		while ( have_posts() ) : the_post();
			$points .= '[' . get_post_meta($post->ID, 'geo_latitude', true) . ',' . get_post_meta($post->ID, 'geo_longitude', true) . '],';
            $post_titles .= '"'.$post->post_title.'",';
            $post_as .= '"<a href=\"'.get_permalink($post->ID).'\">'.$post->post_title.'</a>",';
		endwhile;

        $points = substr($points, 0, -1); // Remove the initial '(' and final '),'
	?>

    <script type="text/javascript">
        ymaps.ready(function () {
            var myMap = new ymaps.Map('mymap1', {
                    center: [55.751574, 37.573856],
                    zoom: 9,
                    controls: ['zoomControl', 'fullscreenControl']
                }),
                clusterer = new ymaps.Clusterer({
                    preset: 'islands#invertedVioletClusterIcons',
                    clusterHideIconOnBalloonOpen: false,
                    geoObjectHideIconOnBalloonOpen: false
                });

            /**
             * Кластеризатор расширяет коллекцию, что позволяет использовать один обработчик
             * для обработки событий всех геообъектов.
             * Будем менять цвет иконок и кластеров при наведении.
             */
            clusterer.events
                // Можно слушать сразу несколько событий, указывая их имена в массиве.
                .add(['mouseenter', 'mouseleave'], function (e) {
                    var target = e.get('target'),
                        type = e.get('type');
                    if (typeof target.getGeoObjects != 'undefined') {
                        // Событие произошло на кластере.
                        if (type == 'mouseenter') {
                            target.options.set('preset', 'islands#invertedPinkClusterIcons');
                        } else {
                            target.options.set('preset', 'islands#invertedVioletClusterIcons');
                        }
                    } else {
                        // Событие произошло на геообъекте.
                        if (type == 'mouseenter') {
                            target.options.set('preset', 'islands#pinkIcon');
                        } else {
                            target.options.set('preset', 'islands#violetIcon');
                        }
                    }
                });

            clusterer.options.set({
                gridSize: 256
            });

            var getPointData = function (index) {
//                    return {
//                        balloonContentBody: 'балун <strong>метки ' + index + '</strong>',
//                        clusterCaption: 'метка <strong>' + index + '</strong>'
//                    };
                    return {
                        balloonContentBody: post_as[index],
                        clusterCaption: 'метка <strong>' + index + '</strong>'
                    };
                },
                getPointOptions = function () {
                    return {
                        preset: 'islands#violetIcon'
                    };
                },
                points = [
                    <?php echo($points); ?>
                ],
                geoObjects = [];

                post_titles = [
                    <?php echo($post_titles); ?>
                ];

                post_as = [
                    <?php echo($post_as); ?>
                ];

            for(var i = 0, len = points.length; i < len; i++) {
                geoObjects[i] = new ymaps.Placemark(points[i], getPointData(i), getPointOptions());
            }

            clusterer.add(geoObjects);
            myMap.geoObjects.add(clusterer);

            myMap.setBounds(clusterer.getBounds(), {
                checkZoomRange: true
            });
        });
    </script>

<!-- <?php edit_post_link('Edit this entry.', '<p>', '</p>'); ?> -->

</div>

<!--
<div id="main">
    <?php get_search_form(); ?>
    <h2>Archives by Month:</h2>
    <ul>
        <?php wp_get_archives('type=monthly'); ?>
    </ul>

    <h2>Archives by Subject:</h2>
    <ul>
        <?php wp_list_categories(); ?>
    </ul>
</div>
-->

<?php get_footer(); ?>