<?php
/**
 * phpwcms content management system
 *
 * @author Oliver Georgi <og@phpwcms.org>
 * @copyright Copyright (c) 2002-2026, Oliver Georgi
 * @license http://opensource.org/licenses/GPL-2.0 GNU GPL-2
 * @link http://www.phpwcms.org
 *
 **/

if (!defined('PHP8')) {
    die("You Cannot Access This Script Directly, Have a Nice Day.");
}

?>
                    <h1 class="h3">phpwcms content values</h1>

                    <form action="setup.php?step=4" method="post">

                        <div class="row mb-3">
                            <label for="file_maxsize" class="col-md-3 col-form-label">upload file size</label>
                            <div class="col-md-9">
                                <input name="file_maxsize" type="text" class="form-control" id="file_maxsize" value="<?php echo $phpwcms["file_maxsize"] ?>" maxlength="100">
                                <div class="form-text">default: 2 x 1024 x 1024 = 2097152 Bytes &#8212; this is the max size of files that can be uploaded</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="content_width" class="col-md-3 col-form-label">content width</label>
                            <div class="col-md-9">
                                <input name="content_width" type="text" class="form-control" id="content_width" value="<?php echo $phpwcms["content_width"] ?>" maxlength="100">
                                <div class="form-text">default: 538 &#8212; the width of the frontend main content block, base value of creating image lists</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <span class="col-md-3 col-form-label">list image</span>
                            <div class="col-md-9">
                                <div class="row g-2 align-items-center">
                                    <div class="col-auto">
                                        <label class="visually-hidden" for="img_list_width">list image width</label>
                                        <input name="img_list_width" type="text" class="form-control" id="img_list_width" value="<?php echo $phpwcms["img_list_width"] ?>" maxlength="100">
                                    </div>
                                    <div class="col-auto">x</div>
                                    <div class="col-auto">
                                        <label class="visually-hidden" for="img_list_height">list image height</label>
                                        <input name="img_list_height" type="text" class="form-control" id="img_list_height" value="<?php echo $phpwcms["img_list_height"] ?>" maxlength="100">
                                    </div>
                                </div>
                                <div class="form-text">default: width 100 x height 75 &#8212; width and height of list image thumbnails</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <span class="col-md-3 col-form-label">preview image</span>
                            <div class="col-md-9">
                                <div class="row g-2 align-items-center">
                                    <div class="col-auto">
                                        <label class="visually-hidden" for="img_prev_width">preview image width</label>
                                        <input name="img_prev_width" type="text" class="form-control" id="img_prev_width" value="<?php echo $phpwcms["img_prev_width"] ?>" maxlength="100">
                                    </div>
                                    <div class="col-auto">x</div>
                                    <div class="col-auto">
                                        <label class="visually-hidden" for="img_prev_height">preview image height</label>
                                        <input name="img_prev_height" type="text" class="form-control" id="img_prev_height" value="<?php echo $phpwcms["img_prev_height"] ?>" maxlength="100">
                                    </div>
                                </div>
                                <div class="form-text">default: width 538 x height 400 &#8212; width and height of preview images, recommend is to use the same width as main content block. For better quality use larger values; if the source image is smaller it will not be sized to these larger values</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="max_time" class="col-md-3 col-form-label">logout time</label>
                            <div class="col-md-9">
                                <input name="max_time" type="text" class="form-control" id="max_time" value="<?php echo $phpwcms["max_time"] ?>" maxlength="100">
                                <div class="form-text">default: 1800 seconds &#8212; if there is no activity within the backend you will be automatically logged out after that time</div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" name="Submit" value="send content values" class="btn btn-primary">send content values</button>
                        </div>
                        <input name="do" type="hidden" value="1">
                    </form>
