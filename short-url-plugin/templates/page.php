<!doctype html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <?php wp_head(); ?>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    </head>

    <body <?php body_class(); ?>>
        <?php wp_body_open(); ?>
            <div class="wrap d-flex justify-content-center">
                <div>
                    <h1 class="display-4">Short URL Plugin</h1>
                    <form id="short-url-form" class="short-url-form mt-4" method="POST" action="<?php echo esc_url(home_url('short-url')); ?>">
                        <?php wp_nonce_field('short-url-plugin-nonce', 'security'); ?>
                        <div class="form-group">
                            <input type="text" class="form-control" name="url" placeholder="Enter URL">
                        </div>
                        <div id="short-url-result" class="form-group"></div>

                        <div class="form-group  text-center">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php wp_footer(); ?>
    </body>
</html>