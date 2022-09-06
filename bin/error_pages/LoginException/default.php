<?php

use spitfire\core\Environment;
use spitfire\io\stp\SimpleStackTracePrinter;

?><!DOCTYPE html>
<html>
	<head>
		<title>Spitfire - User Banned</title>
		<style><?php readfile('./assets/css/app.css'); ?></style>
	</head>
	<body>
		<div class="min-h-screen">
			<div class="bg-blue-900 text-center py-4 lg:px-4 text-sm">
				<div class="p-2 bg-blue-800 items-center text-blue-100 leading-none lg:rounded-full flex lg:inline-flex" role="alert">
					<span class="flex rounded-full bg-blue-500 uppercase px-2 py-1 text-xs font-bold mr-3">Important</span>
					<span class="font-semibold mr-2 text-left flex-auto">
						When contacting support, please remember to include your user information
					</span>
				</div>
			</div>
			<div class="h-16"></div>
			
			<div class="container mx-auto max-w-screen-md px-6">
				<img src="<?= url('image', 'hero') ?>">
			</div>
			
			<div class="h-16"></div>
		
			<div class="container mx-auto max-w-screen-md px-4">
				
				<div class="px-4">
					<p class="text-xs text-slate-500 flex items-center">
						<?php if ($exception->getExpiry() > 0 && $exception->getExpiry() < time() + (365 * 86400)) :?>
						<svg xmlns="http://www.w3.org/2000/svg" class="fill-current text-slate-500 w-3 h-3 mr-2" viewBox="0 0 20 20" fill="currentColor">
							<path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
						</svg>
							<?php echo (new DateTime('now'))->diff(new DateTime('@'.$exception->getExpiry()))->format('%a days, %h hours and %i minutes'); ?>
						<?php endif; ?>
						<svg xmlns="http://www.w3.org/2000/svg" class="fill-current text-slate-500 w-3 h-3 mr-2 ml-4" viewBox="0 0 20 20" fill="currentColor">
							<path d="M13 7H7v6h6V7z" />
							<path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2h-2v1a1 1 0 11-2 0v-1H9v1a1 1 0 11-2 0v-1H5a2 2 0 01-2-2v-2H2a1 1 0 110-2h1V9H2a1 1 0 010-2h1V5a2 2 0 012-2h2V2zM5 5h10v10H5V5z" clip-rule="evenodd" />
						</svg>
						<?=str_pad($exception? $exception->getUserID() : '', 10, '0', STR_PAD_LEFT) ?> - <?=date(DATE_ATOM) ?>
					</p>
				</div>
				<div class="border b-slate-300 rounded-lg p-4">
					
					<h1 class="font-bold text-black"><?= __($message) ?></h1>
					
					<h2 class="text-white"></h2>
					<p class="text-base"><?= $exception? __($exception->getReason()) : '' ?></p>
				</div>
			
				<div class="h-6"></div>
				
				<div class="px-4">
					<p>
						If you have any questions about this, please <a class="text-sky-400" href="<?= Environment::get('support.url') ?>">contact</a> 
						our support team. Please remember to always provide the necessary information for staff to help you, this includes your user
						information (like email or username) and the necessary information to lift the suspension. Continue reading for more.
					</p>
				</div>
				
				<div class="h-24"></div>
				
				<div class="text-center">
					<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline-block text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
						<path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
					</svg>
				</div>
			</div>
		</div>
		
		
		<div class="container mx-auto max-w-screen-md px-4">
			<div class="h-24"></div>
			
			<div class="px-4">
				<h1 class="font-bold text-black text-3xl">Tips and tricks when reaching out to support</h1>
				<div class="h-2"></div>
				<p class="text-lg text-slate-700 leading-relaxed">
					Getting suspended can be scary. It's okay. This guide is here
					to help you get your account back. Following these steps improves the process
					for you and us.
				</p>
			</div>
			<div class="h-16"></div>
			
			<div class="px-4 prose">
				<div class="rounded-full bg-sky-300 text-white text-3xl text-center w-12 h-12 flex items-center justify-center">1.</div>
				<div class="h-3"></div>
				<span class="text-sm text-indigo-600 font-bold">Make some coffee or tea, set aside a few minutes and</span>
				<h2 class="font-bold text-black text-xl">Be patient and thorough in your contact</h2>
				<div class="h-3"></div>
				<p class="text-base text-slate-700 leading-relaxed">
					Support may take hours or even days to take care of your issue. We hope you can
					understand that. In order to help you as fast as possible it helps reducing the
					round-trips. If support needs to answer to your email asking for additional 
					information, they will have to wait for you to reply and then you will have to wait
					for them again. Your issue will take longer to resolve. So make sure you gather 
					all the documentation before you reach out.
				</p>
			</div>
			
			
			<div class="h-16"></div>
			<div class="border-b border-b-slate-200"></div>
			<div class="h-16"></div>
			
			<div class="px-4 prose">
				<div class="rounded-full bg-sky-300 text-white text-3xl text-center w-12 h-12 flex items-center justify-center">2.</div>
				<div class="h-3"></div>
				<span class="text-sm text-indigo-600 font-bold">Look at the ban reason and</span>
				<h2 class="font-bold text-black text-xl">Provide information needed to lift the ban</h2>
				<div class="h-3"></div>
				<p class="text-base text-slate-700 leading-relaxed">
					This page provides a reason why your account was suspended. Please read the reason
					carefully and make sure you understand. Then, make sure to provide as much information
					as possible to let a moderator understand why the suspension was either wrong or, what
					steps you're taking to not repeat the issue. 
				</p>
				<div class="h-3"></div>
				<p class="text-base text-slate-700 leading-relaxed">
					Suspensions are not handled by bots, a member of staff reviewed a complaint and suspended 
					your account. Assume that the staff member has evidence available to proof you violated a
					rule or guideline. If the ban message is asking you for additional information, please
					make sure to provide it.
				</p>
				<div class="h-3"></div>
				<p class="text-base text-slate-700 leading-relaxed">
					Note that, if your infraction was severe, your account cannot be restored. This includes
					infractions like harassment, copyright infringement, and other legal issues.
				</p>
			</div>
			
			<div class="h-16"></div>
			<div class="border-b border-b-slate-200"></div>
			<div class="h-16"></div>
			
			<div class="px-4 prose">
				<div class="rounded-full bg-sky-300 text-white text-3xl text-center w-12 h-12 flex items-center justify-center">3.</div>
				<div class="h-3"></div>
				<span class="text-sm text-indigo-600 font-bold">Take a deep breath and</span>
				<h2 class="font-bold text-black text-xl">Be kind towards staff</h2>
				<div class="h-3"></div>
				<p class="text-base text-slate-700 leading-relaxed">
					This website is run by humans, they have feelings. When reaching out to staff
					you should remember that. Being kind to them will make them want to help you and 
					will further your case. Suspensions are nothing personal.
				</p>
				<div class="h-3"></div>
				<p class="text-base text-slate-700 leading-relaxed">
					If you're currently angry about this, please take a bit of time to unwind before
					getting in touch. Taking a walk, listening to music or watching a movie will help 
					you get some perspective and tackle it with a fresh mind.
				</p>
				<div class="h-3"></div>
				<p class="text-base text-slate-700 leading-relaxed">
					Even if staff made a mistake, it's never helpful to be talk someone down. If you 
					come off as agressive or brash, the person on the other hand will have a harder
					time admitting their mistake and will be more reluctant to help. Unsolicitedly contacting
					staff outside of the official channels will not further your case.
				</p>
			</div>
			
			
			<div class="h-16"></div>
			<div class="border-b border-b-slate-200"></div>
			<div class="h-16"></div>
			
			<div class="px-4">
				
				<p class="text-base text-slate-700 leading-relaxed">
					We hope these steps help you resolving the issue, and that you get your account back as
					soon as possible. Thank you for helping and being respectful!
				</p>
			</div>
			<div class="h-24"></div>
			<div class="text-center text-sm text-slate-500">
				&copy;<?= date('Y') ?> Magic3w - Software licensed under LGPL
			</div>
		</div>
</body>
</html>
