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
        {{ assets.outputCss('header') }}
        
    </head>
    <body>
        <nav class="navbar navbar-default navbar-inverse" role="navigation">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#">Frontend</a>
                </div>
                {{ elements.getMenu() }}
            </div>
        </nav>

        <div class="container-fluid">
        {{ flash.output() }}
        {{ content() }}
        <hr />
        <footer>
            <p>DataEngine Frontend</p>
        </footer>
        </div>

        {{ javascript_include('js/jquery-3.1.0.min.js') }}
        {{ javascript_include('js/bootstrap.min.js') }}
        {{ assets.outputJs('footer') }}
    </body>
</html>
