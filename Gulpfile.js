// jshint ignore: start
var cl = console.log,
	chalk = require('chalk');
console.log = console.writeLine = function () {
	var args = [].slice.call(arguments), match;
	if (args.length){
		if (/^(\[\d{2}:\d{2}:\d{2}]|Using|Finished)/.test(args[0]))
			return;
		else if (args[0] == 'Starting' && (match = args[1].match(/^'.*((?:dist-)?js|scss|default|md).*'...$/))){
			args = ['[' + chalk.green('gulp') + '] ' + match[1] + ': ' + chalk.magenta('start')];
		}
	}
	return cl.apply(console, args);
};
var stdoutw = process.stdout.write;
process.stdout.write = console.write = function(str){
	var out = [].slice.call(arguments).join(' ');
	if (/\[.*?\d{2}.*?:.*?]/.test(out))
		return;
	stdoutw.call(process.stdout, out);
};

var toRun = process.argv.slice(2).slice(-1)[0];
if (!toRun || /[^a-z]/.test(toRun))
	toRun = 'default';
console.writeLine('Starting Gulp task "'+toRun+'"');
var require_list = ['gulp'];
if (['js','scss','default'].indexOf(toRun) !== -1){
	require_list.push.apply(require_list, [
		'gulp-plumber',
		'gulp-duration',
		'gulp-sourcemaps',
	]);

	if (toRun === 'scss' || toRun === 'default')
		require_list.push.apply(require_list, [
			'gulp-sass',
			'gulp-autoprefixer',
			'gulp-minify-css',
		]);
	if (toRun === 'js' || toRun === 'default')
		require_list.push.apply(require_list, [
			'gulp-uglify',
			'gulp-babel',
			'gulp-cached',
			'gulp-rename'
		]);
}
console.write('(');
for (var i= 0,l=require_list.length; i<l; i++){
	var v = require_list[i];
	global[v.replace(/^gulp-([a-z]+).*$/, '$1')] = require(v);
	console.write(' '+v);
}
console.writeLine(" )\n");

var workingDir = __dirname;

function Logger(prompt){
	var $p = '['+chalk.blue(prompt)+'] ';
	this.log = function(message){
		console.writeLine($p+message);
	};
	this.error = function(message){
		if (typeof message === 'string'){
			message = message.trim()
				.replace(/[\/\\]?www/,'');
			console.error($p+'Error in '+message);
		}
		else console.log(JSON.stringify(message,null,'4'));
	};
	return this;
}

var SASSL = new Logger('scss');
gulp.task('scss', function() {
	gulp.src('assets/scss/*.scss')
		.pipe(plumber(function(err){
			SASSL.error(err.relativePath+'\n'+'  line '+err.line+': '+err.messageOriginal);
			this.emit('end');
		}))
		.pipe(sourcemaps.init())
			.pipe(sass({
				outputStyle: 'expanded',
				errLogToConsole: true,
			}))
			.pipe(autoprefixer('last 2 version'))
			.pipe(minify({
				processImport: false,
				compatibility: '-units.pc,-units.pt'
			}))
		.pipe(sourcemaps.write('.', {
			includeContent: false,
			sourceRoot: '/assets/scss',
		}))
		.pipe(duration('scss'))
		.pipe(gulp.dest('assets/css'));
});

var JSL = new Logger('js'),
	JSWatchArray = ['assets/js/*.js','!assets/js/*.min.js'];
gulp.task('js', function(){
	gulp.src(JSWatchArray)
		.pipe(duration('js'))
		.pipe(cached('js', { optimizeMemory: true }))
		.pipe(plumber(function(err){
			err =
				err.fileName
				? err.fileName.replace(workingDir,'')+'\n  line '+(
					err._babel === true
					? err.loc.line
					: err.lineNumber
				)+': '+err.message.replace(/^[\/\\]/,'')
				                  .replace(err.fileName.replace(/\\/g,'/')+': ','')
				                  .replace(/\(\d+(:\d+)?\)$/, '')
				: err;
			JSL.error(err);
			this.emit('end');
		}))
		.pipe(sourcemaps.init())
			.pipe(babel({
				presets: ['es2015']
			}))
			.pipe(uglify({
				preserveComments: function(_, comment){ return /^!/m.test(comment.value) },
				output: { ascii_only: true },
			}))
			.pipe(rename(function(path){
				path.basename += '.min';
			}))
		.pipe(sourcemaps.write('.', {
			includeContent: false,
			sourceRoot: '/assets/js',
		}))
		.pipe(gulp.dest('assets/js'));
});

gulp.task('default', ['js', 'scss'], function(){
	gulp.watch(JSWatchArray, {debounceDelay: 2000}, ['js']);
	JSL.log('File watcher active');
	gulp.watch('assets/scss/*.scss', {debounceDelay: 2000}, ['scss']);
	SASSL.log('File watcher active');
});
