<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        {{ get_title() }}
        {{ stylesheet_link('css/bootstrap.min.css') }}
        {{ stylesheet_link('css/bootstrap-theme.min.css') }}
        {{ stylesheet_link('css/common.css') }}
        {{ stylesheet_link('css/navbar.css') }}
        {{ assets.outputCss() }}
        
    </head>
    <body>
        <nav class="navbar navbar-default navbar-inverse navbar-fixed-top" role="navigation">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="/">AMPortal</a>
                </div>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav navbar-right">
                    {{ navigation.getMenu('navbar-right') }}
                    </ul>
                    <ul class="nav navbar-nav">
                    {{ navigation.getMenu('navbar-left') }}
                    </ul>
                </div>
            </div>
        </nav>
        <!-- Empty block for padding down -->
        <div class="container" style="height:56px; display:block"></div>
        
        <div class="container-fluid">
            {{ flash.output() }}
            {{ content() }}
            <hr />
            <footer>
                <p>AMPortal Frontend</p>
            </footer>
        </div>

        {{ javascript_include('js/jquery-3.1.0.min.js') }}
        {{ javascript_include('js/bootstrap.min.js') }}
        {{ javascript_include('js/common.js') }}
        {{ javascript_include('js/navbar.js') }}
        {{ assets.outputJs() }}
    </body>
</html>
