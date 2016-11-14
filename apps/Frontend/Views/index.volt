<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        {{ get_title() }}
        {{ stylesheet_link('css/bootstrap.min.css') }}
        {{ stylesheet_link('css/bootstrap.theme.min.css') }}
        {{ assets.outputCss() }}
        
    </head>
    <body>
        <nav class="navbar navbar-default navbar-inverse navbar-static-top" role="navigation">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href="#">Frontend</a>
                </div>
                {{ elements.getMenu() }}
            </div>
        </nav>

        <div class="container-fluid">
            <!-- Flash -->
            {{ flash.output() }}
            <!-- End Flash -->

            {{ content() }}
            <hr />
            <footer>
                <p>DataEngine Frontend</p>
            </footer>
        </div>

        {{ javascript_include('js/jquery-3.1.0.min.js') }}
        {{ javascript_include('js/bootstrap.min.js') }}
        {{ assets.outputJs() }}
    </body>
</html>
