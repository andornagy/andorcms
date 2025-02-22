<?php loadPartial('head'); ?>
<?php loadPartial('navbar'); ?>
<?php loadPartial('top-banner'); ?>

<!-- Post a Job Form Box -->
<section class="flex justify-center items-center mt-20">
    <div class="bg-white p-8 rounded-lg shadow-md w-full md:w-600 mx-6">
        <h2 class="text-4xl text-center font-bold mb-4">Edit Job Listing</h2>

        <form method="POST" action="/listings/<?= $listing->post_id ?>">
            <input type="hidden" name="_method" value="PUT">
            <h2 class="text-2xl font-bold mb-6 text-center text-gray-500">
                Job Info
            </h2>

            <?= loadPartial('errors', ['errors' => $errors ?? []]); ?>

            <div class="mb-4">
                <input type="text" name="title" placeholder="Job Title" class="w-full px-4 py-2 border rounded focus:outline-none" value="<?= $listing->title ?? '' ?>" />
            </div>
            <div class="mb-4">
                <textarea name="content" placeholder="Job Description" class="w-full px-4 py-2 border rounded focus:outline-none"><?= $listing->content ?? '' ?></textarea>
            </div>
            <div class="mb-4">
                <input type="text" name="salary" placeholder="Annual Salary" class="w-full px-4 py-2 border rounded focus:outline-none" value="<?= $postMeta->salary ?? '' ?>" />
            </div>
            <div class="mb-4">
                <input type="text" name="requirements" placeholder="Requirements" class="w-full px-4 py-2 border rounded focus:outline-none" value="<?= $postMeta->requirements ?? '' ?>" />
            </div>
            <div class="mb-4">
                <input type="text" name="benefits" placeholder="Benefits" class="w-full px-4 py-2 border rounded focus:outline-none" value="<?= $postMeta->benefits ?? '' ?>" />
            </div>
            <div class="mb-4">
                <input type="text" name="tags" placeholder="Tags" class="w-full px-4 py-2 border rounded focus:outline-none" value="<?= $postMeta->tags ?? '' ?>" />
            </div>
            <h2 class="text-2xl font-bold mb-6 text-center text-gray-500">
                Company Info & Location
            </h2>
            <div class="mb-4">
                <input type="text" name="company" placeholder="Company Name" class="w-full px-4 py-2 border rounded focus:outline-none" value="<?= $postMeta->company ?? '' ?>" />
            </div>
            <div class="mb-4">
                <input type="text" name="address" placeholder="Address" class="w-full px-4 py-2 border rounded focus:outline-none" value="<?= $postMeta->address ?? '' ?>" />
            </div>
            <div class="mb-4">
                <input type="text" name="city" placeholder="City" class="w-full px-4 py-2 border rounded focus:outline-none" value="<?= $postMeta->city ?? '' ?>" />
            </div>
            <div class="mb-4">
                <input type="text" name="state" placeholder="State" class="w-full px-4 py-2 border rounded focus:outline-none" value="<?= $postMeta->state ?? '' ?>" />
            </div>
            <div class="mb-4">
                <input type="text" name="phone" placeholder="Phone" class="w-full px-4 py-2 border rounded focus:outline-none" value="<?= $postMeta->phone ?? '' ?>" />
            </div>
            <div class="mb-4">
                <input type="email" name="email" placeholder="Email Address For Applications" class="w-full px-4 py-2 border rounded focus:outline-none" value="<?= $postMeta->email ?? '' ?>" />
            </div>
            <button class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 my-3 rounded focus:outline-none">
                Save
            </button>
            <a href="/listings/<?= $listing->post_id ?>" class="block text-center w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded focus:outline-none">
                Cancel
            </a>
        </form>
    </div>
</section>


<?php loadPartial('bottom-banner'); ?>
<?php loadPartial('footer'); ?>