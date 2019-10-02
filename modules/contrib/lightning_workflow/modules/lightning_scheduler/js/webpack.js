const env_options = {
    modules: false,
    targets: {
        browsers: ['last 2 versions']
    }
};

module.exports = {

    entry: ['core-js/fn/object/entries', './index.es6.js'],

    output: {
        filename: 'index.js',
        path: require('path').resolve(__dirname, 'dist')
    },

    externals: {
        react: 'React',
        'react-dom': 'ReactDOM'
    },

    devtool: 'source-map',

    module: {
        loaders: [
            {
                test: /.es6.js$/,
                loader: 'babel-loader',
                exclude: ['/node_modules/'],
                options: {
                    presets: ['react', ['env', env_options]]
                }
            }
        ]
    }

};
