Customizing GitZenith is pretty easy since it was built on top of wonderful, modern and open-source projects. 

## Styles

The GitZenith gorgeous interface was built using [Twitter Bootstrap](http://twitter.github.com/bootstrap/) and it leverages the power of [LESS](http://lesscss.org/) to make your life easier if you want to customize the look of GitZenith. The LESS files are available under `themes/<theme_name>/less`. A makefile is provided, so all you have to do is customize the LESS based on your taste, run `make` under the `<theme_name>` folder and the final CSS will be generated. Of course, to make this possible, you need `lessc` installed, which can be done quite easily by running [npm](https://github.com/isaacs/npm): `npm install less`

## Templates

GitZenith was built on on top of the [Silex](http://silex.sensiolabs.org/) microframework and powered by the [Twig](http://twig.sensiolabs.org/) template engine. All templates are available under `themes/<theme_name>/twig`. In order to understand what is going on, I recommend that you read this [tutorial](http://twig.sensiolabs.org/doc/templates.html). Enable debug in your `config.ini` or clean the contents of the `cache` folder to see `.twig` files changes!