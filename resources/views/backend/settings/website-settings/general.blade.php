@extends('backend.master')

@section('title', 'General Settings')

@section('content')

<div class="row">
    <div class="col-4 col-sm-2">
        <div class="nav flex-column nav-tabs h-100" id="vert-tabs-tab" role="tablist" aria-orientation="vertical">
            @can('website_settings')
            <a class="nav-link {{ @$_GET['active-tab'] == 'website-info' ? 'active' : '' }}" id="vert-tabs-1"
                data-toggle="pill" href="#tabs-1" role="tab" aria-controls="tabs-1" aria-selected="true">
                <i class="fas fa-desktop"></i>
                &nbsp;Website Info
            </a>
            @endcan
            @can('contact_settings')
            <a class="nav-link {{ @$_GET['active-tab'] == 'contacts' ? 'active' : '' }}" id="vert-tabs-2"
                data-toggle="pill" href="#tabs-2" role="tab" aria-controls="tabs-2" aria-selected="false">
                <i class="fas fa-address-book"></i>
                &nbsp;Contacts
            </a>
            @endcan
            @can('socials_settings')
            <a class="nav-link {{ @$_GET['active-tab'] == 'social-links' ? 'active' : '' }}" id="vert-tabs-3"
                data-toggle="pill" href="#tabs-3" role="tab" aria-controls="tabs-3" aria-selected="false">
                <i class="fas fa-share-alt"></i>
                &nbsp;Social Links
            </a>
            @endcan
            @can('style_settings')
            <a class="nav-link {{ @$_GET['active-tab'] == 'style-settings' ? 'active' : '' }}" id="vert-tabs-4"
                data-toggle="pill" href="#tabs-4" role="tab" aria-controls="tabs-4" aria-selected="false">
                <i class="fas fa-swatchbook"></i>
                &nbsp;Style Settings
            </a>
            @endcan
            @can('custom_settings')
            <a class="nav-link {{ @$_GET['active-tab'] == 'custom-css' ? 'active' : '' }}" id="vert-tabs-5"
                data-toggle="pill" href="#tabs-5" role="tab" aria-controls="tabs-5" aria-selected="false">
                <i class="fas fa-code"></i>
                &nbsp;Custom CSS
            </a>
            @endcan
            @can('notification_settings')
            <a class="nav-link {{ @$_GET['active-tab'] == 'notification-settings' ? 'active' : '' }}" id="vert-tabs-6"
                data-toggle="pill" href="#tabs-6" role="tab" aria-controls="tabs-6" aria-selected="false">
                <i class="fas fa-envelope"></i>
                &nbsp;Notification Settings
            </a>
            @endcan
            @can('website_status_settings')
            <a class="nav-link {{ @$_GET['active-tab'] == 'website-status' ? 'active' : '' }}" id="vert-tabs-7"
                data-toggle="pill" href="#tabs-7" role="tab" aria-controls="tabs-7" aria-selected="false">
                <i class="fas fa-power-off"></i>
                &nbsp;Website Status
            </a>
            @endcan
            @can('invoice_settings')
            <a class="nav-link {{ @$_GET['active-tab'] == 'invoice-settings' ? 'active' : '' }}" id="vert-tabs-8"
                data-toggle="pill" href="#tabs-8" role="tab" aria-controls="tabs-8" aria-selected="false">
                <i class="fas fa-file-invoice"></i>
                &nbsp;Invoice Settings
            </a>
            @endcan
            @can('system_update_settings')
            <a class="nav-link {{ @$_GET['active-tab'] == 'system-update' ? 'active' : '' }}" id="vert-tabs-9"
                data-toggle="pill" href="#tabs-9" role="tab" aria-controls="tabs-9" aria-selected="false">
                <i class="fas fa-sync-alt"></i>
                &nbsp;System Update
            </a>
            @endcan
        </div>
    </div>
    <div class="col-8 col-sm-10">
        <div class="tab-content" id="vert-tabs-tabContent">
            @can('website_settings')
            <div class="tab-pane fade {{ @$_GET['active-tab'] == 'website-info' ? 'active show' : '' }}" id="tabs-1"
                role="tabpanel" aria-labelledby="vert-tabs-1">

                <form action="{{ route('backend.admin.settings.website.info.update') }}" method="post">
                    @csrf
                    <div class="col-md-12 d-flex justify-content-between">
                        <h5>
                            <i class="fas fa-desktop"></i>
                            &nbsp;&nbsp;Website Info
                        </h5>
                        <button type="submit" class="btn bg-gradient-primary">
                            <i class="fas fa-reply"></i>
                            &nbsp;Save Changes
                        </button>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Website Title</label>
                            <input class="form-control" name="site_name" type="text"
                                value="{{ readConfig('site_name') }}" placeholder="Enter Site Title">
                        </div>
                        <div class="form-group">
                            <label>Meta Description</label>
                            <textarea class="form-control" rows="2" name="meta_description" cols="50"
                                placeholder="Enter Meta Description">{{ readConfig('meta_description') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Meta Keywords</label>
                            <textarea class="form-control" rows="2" name="meta_keywords" cols="50" placeholder="Enter Keywords">{{ readConfig('meta_keywords') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Website URL</label>
                            <input class="form-control" name="site_url" type="url"
                                value="{{ readConfig('site_url') }}" placeholder="Enter Site URL">
                        </div>
                    </div>
                </form>

            </div>
            @endcan
            @can('contact_settings')
            <div class="tab-pane fade {{ @$_GET['active-tab'] == 'contacts' ? 'active show' : '' }}" id="tabs-2"
                role="tabpanel" aria-labelledby="vert-tabs-2">

                <form action="{{ route('backend.admin.settings.website.contacts.update') }}" method="post">
                    @csrf
                    <div class="col-md-12 d-flex justify-content-between">
                        <h5>
                            <i class="fas fa-address-book"></i>
                            &nbsp;&nbsp;Contacts
                        </h5>
                        <button type="submit" class="btn bg-gradient-primary">
                            <i class="fas fa-reply"></i>
                            &nbsp;Save Changes
                        </button>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Address</label>
                            <input placeholder="" class="form-control" name="contact_address" type="text"
                                value="{{ readConfig('contact_address') }}">
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input placeholder="Phone" class="form-control" name="contact_phone" type="tel"
                                value="{{ readConfig('contact_phone') }}">
                        </div>
                        <div class="form-group">
                            <label>Fax</label>
                            <input placeholder="Fax" class="form-control" name="contact_fax" type="tel"
                                value="{{ readConfig('contact_fax') }}">
                        </div>
                        <div class="form-group">
                            <label>Mobile</label>
                            <input placeholder="Mobile" class="form-control" name="contact_mobile" type="tel"
                                value="{{ readConfig('contact_mobile') }}">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input placeholder="Email" class="form-control" name="contact_email" type="email"
                                value="{{ readConfig('contact_email') }}">
                        </div>
                        <div class="form-group">
                            <label>Working Time</label>
                            <input placeholder="Sunday to Thursday 08:00 AM to 05:00 PM" class="form-control"
                                name="working_hour" type="text" value="{{ readConfig('working_hour') }}">
                        </div>
                    </div>
                </form>

            </div>
            @endcan
            @can('socials_settings')
            <div class="tab-pane fade {{ @$_GET['active-tab'] == 'social-links' ? 'active show' : '' }}"
                id="tabs-3" role="tabpanel" aria-labelledby="vert-tabs-3">
                <form action="{{ route('backend.admin.settings.website.social.link.update') }}" method="post">
                    @csrf
                    <div class="col-md-12 d-flex justify-content-between">
                        <h5>
                            <i class="fas fa-share-alt"></i>
                            &nbsp;&nbsp;Social Links
                        </h5>
                        <button type="submit" class="btn bg-gradient-primary">
                            <i class="fas fa-reply"></i>
                            &nbsp;Save Changes
                        </button>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>
                                <i class="fab fa-facebook"></i>
                                &nbsp; Facebook
                            </label>
                            <input placeholder="Facebook" class="form-control" name="facebook_link" type="url"
                                value="{{ readConfig('facebook_link') }}">
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fab fa-twitter"></i>
                                &nbsp; Twitter
                            </label>
                            <input placeholder="Twitter" class="form-control" name="twitter_link" type="url"
                                value="{{ readConfig('twitter_link') }}">
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fab fa-linkedin"></i>
                                &nbsp; Linkedin
                            </label>
                            <input placeholder="Linkedin" class="form-control" name="linkedin_link" type="url"
                                value="{{ readConfig('linkedin_link') }}">
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fab fa-youtube"></i>
                                &nbsp; Youtube
                            </label>
                            <input placeholder="Youtube" class="form-control" name="youtube_link" type="url"
                                value="{{ readConfig('youtube_link') }}">
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fab fa-instagram"></i>
                                &nbsp; Instagram
                            </label>
                            <input placeholder="Instagram" class="form-control" name="instagram_link" type="url"
                                value="{{ readConfig('instagram_link') }}">
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fab fa-pinterest"></i>
                                &nbsp; Pinterest
                            </label>
                            <input placeholder="Pinterest" class="form-control" name="pinterest_link" type="url"
                                value="{{ readConfig('pinterest_link') }}">
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fab fa-tumblr"></i>
                                &nbsp; Tumblr
                            </label>
                            <input placeholder="Tumblr" class="form-control" name="tumblr_link" type="url"
                                value="{{ readConfig('tumblr_link') }}">
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fab fa-snapchat"></i>
                                &nbsp; Snapchat
                            </label>
                            <input placeholder="Snapchat" class="form-control" name="snapchat_link" type="url"
                                value="{{ readConfig('snapchat_link') }}">
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fab fa-whatsapp"></i>
                                &nbsp; Whatsapp
                            </label>
                            <input placeholder="Whatsapp" class="form-control" name="whatsapp_link" type="url"
                                value="{{ readConfig('whatsapp_link') }}">
                        </div>
                    </div>
                </form>
            </div>
            @endcan
            @can('style_settings')
            <div class="tab-pane fade {{ @$_GET['active-tab'] == 'style-settings' ? 'active show' : '' }}"
                id="tabs-4" role="tabpanel" aria-labelledby="vert-tabs-4">

                <form action="{{ route('backend.admin.settings.website.style.settings.update') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="col-md-12 d-flex justify-content-between">
                        <h5>
                            <i class="fas fa-swatchbook"></i>
                            &nbsp;&nbsp;Style Settings
                        </h5>
                        <button type="submit" class="btn bg-gradient-primary">
                            <i class="fas fa-reply"></i>
                            &nbsp;Save Changes
                        </button>
                    </div>

                    <div class="col-12 my-2">
                        <label>Site Logo</label>

                        <div class="row">
                            <div class="col-sm-12">
                                <div class="col-sm-12 box p-a-xs text-center">
                                    <img src="{{ assetImage(readconfig('site_logo')) }}"
                                        class="img-fluid thumbnail-preview site-logo-placeholder">
                                </div>
                            </div>
                        </div>
                        <input class="form-control" accept="image/*" name="site_logo" type="file"
                            onchange="previewThumbnail(this)">
                        <small>
                            <i class="far fa-question-circle"></i>
                            ( 260x60 px ) - Extensions: .png, .jpg, .jpeg, .gif, .svg
                        </small>
                    </div>
                    <hr>
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <label for="style_fav">Favicon</label>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="col-sm-12 box p-a-xs text-center">
                                        <a target="_blank" href="{{ assetImage(readconfig('favicon_icon')) }}">
                                            <img src="{{ assetImage(readconfig('favicon_icon')) }}"
                                                class="img-fluid thumbnail-preview site-logo-placeholder">
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <input class="form-control" accept="image/*" name="favicon_icon" type="file"
                                onchange="previewThumbnail(this)">
                            <small>
                                <i class="far fa-question-circle"></i>
                                ( 32x32 px ) - Extensions: .png, .jpg, .jpeg, .gif, .svg
                            </small>
                        </div>
                        <div class="col-sm-6">
                            <label for="style_apple">Apple Icon</label>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="col-sm-12 box p-a-xs text-center">
                                        <a target="_blank" href="{{ assetImage(readconfig('favicon_icon_apple')) }}">
                                            <img src="{{ assetImage(readconfig('favicon_icon_apple')) }}"
                                                class="img-fluid thumbnail-preview site-logo-placeholder">
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <input class="form-control" accept="image/*" name="favicon_icon_apple" type="file"
                                onchange="previewThumbnail(this)">
                            <small>
                                <i class="far fa-question-circle"></i>
                                ( 180x180 px ) - Extensions: .png, .jpg, .jpeg, .gif, .svg
                            </small>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label>Newsletter Subscribe</label>
                        <div class="radio bg-white rounded pt-2 pl-2 border">
                            <label class="ui-check ui-check-md">
                                <input {{ readConfig('newsletter_subscribe') == 1 ? 'checked' : '' }}
                                    name="newsletter_subscribe" type="radio" value="1">
                                <i class="dark-white"></i>
                                Active
                            </label>
                            &nbsp; &nbsp;
                            <label class="ui-check ui-check-md">
                                <input {{ readConfig('newsletter_subscribe') == 0 ? 'checked' : '' }}
                                    name="newsletter_subscribe" type="radio" value="0">
                                <i class="dark-white"></i>
                                Not Active
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            @endcan
            @can('custom_settings')
            <div class="tab-pane fade {{ @$_GET['active-tab'] == 'custom-css' ? 'active show' : '' }}" id="tabs-5"
                role="tabpanel" aria-labelledby="vert-tabs-5">
                <form action="{{ route('backend.admin.settings.website.custom.css.update') }}" method="post">
                    @csrf
                    <div class="col-md-12 d-flex justify-content-between">
                        <h5>
                            <i class="fas fa-code"></i>
                            &nbsp;&nbsp;Custom CSS
                        </h5>
                        <button type="submit" class="btn bg-gradient-primary">
                            <i class="fas fa-reply"></i>
                            &nbsp;Save Changes
                        </button>
                    </div>
                    <div class="col-md-12 mt-2">
                        <div class="form-group">
                            <textarea placeholder="" class="form-control" rows="17" name="custom_css" cols="50">{{ readConfig('custom_css') }}</textarea>
                        </div>
                    </div>
                </form>
            </div>
            @endcan
            @can('notification_settings')
            <div class="tab-pane fade {{ @$_GET['active-tab'] == 'notification-settings' ? 'active show' : '' }}"
                id="tabs-6" role="tabpanel" aria-labelledby="vert-tabs-6">
                <form action="{{ route('backend.admin.settings.website.notification.settings.update') }}"
                    method="post">
                    @csrf
                    <div class="col-md-12 d-flex justify-content-between">
                        <h5>
                            <i class="fas fa-envelope"></i>
                            &nbsp;&nbsp;Notification Settings
                        </h5>
                        <button type="submit" class="btn bg-gradient-primary">
                            <i class="fas fa-reply"></i>
                            &nbsp;Save Changes
                        </button>
                    </div>
                    <div class="p-a-md col-md-12">
                        <div class="form-group">
                            <label>Website Notification Email</label>
                            <input placeholder="Enter email" class="form-control" name="notify_email_address"
                                type="email" value="{{ readConfig('notify_email_address') }}">
                        </div>
                        <div class="form-group">
                            <label>Send me an email on new contact Messages : </label>
                            <div class="radio bg-white rounded pt-2 pl-2 border">
                                <label class="ui-check ui-check-md">
                                    <input {{ readConfig('notify_messages_status') == 1 ? 'checked' : '' }}
                                        name="notify_messages_status" type="radio" value="1">
                                    <i class="dark-white"></i>
                                    Yes
                                </label>
                                &nbsp; &nbsp;
                                <label class="ui-check ui-check-md">
                                    <input {{ readConfig('notify_messages_status') == 0 ? 'checked' : '' }}
                                        name="notify_messages_status" type="radio" value="0">
                                    <i class="dark-white"></i>
                                    No
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Send me an email on new Comments : </label>
                            <div class="radio bg-white rounded pt-2 pl-2 border">
                                <label class="ui-check ui-check-md">
                                    <input {{ readConfig('notify_comments_status') == 1 ? 'checked' : '' }}
                                        name="notify_comments_status" type="radio" value="1">
                                    <i class="dark-white"></i>
                                    Yes
                                </label>
                                &nbsp; &nbsp;
                                <label class="ui-check ui-check-md">
                                    <input {{ readConfig('notify_comments_status') == 0 ? 'checked' : '' }}
                                        name="notify_comments_status" type="radio" value="0">
                                    <i class="dark-white"></i>
                                    No
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            @endcan
            @can('website_status_settings')
            <div class="tab-pane fade {{ @$_GET['active-tab'] == 'website-status' ? 'active show' : '' }}"
                id="tabs-7" role="tabpanel" aria-labelledby="vert-tabs-7">
                <form action="{{ route('backend.admin.settings.website.status.update') }}" method="post">
                    @csrf
                    <div class="col-md-12 d-flex justify-content-between">
                        <h5>
                            <i class="fas fa-power-off"></i>
                            &nbsp;&nbsp;Website Status
                        </h5>
                        <button type="submit" class="btn bg-gradient-primary">
                            <i class="fas fa-reply"></i>
                            &nbsp;Save Changes
                        </button>
                    </div>
                    <div class="p-a-md col-md-12">
                        <div class="form-group">
                            <label>Website Status : </label>
                            <div class="radio bg-white rounded pt-2 pl-2 border">
                                <label class="ui-check ui-check-md">
                                    <input {{ readConfig('is_live') == 1 ? 'checked' : '' }} name="is_live"
                                        type="radio" value="1">
                                    <i class="dark-white"></i>
                                    Active
                                </label>
                                &nbsp; &nbsp;
                                <label class="ui-check ui-check-md">
                                    <input {{ readConfig('is_live') == 0 ? 'checked' : '' }} name="is_live"
                                        type="radio" value="0">
                                    <i class="dark-white"></i>
                                    Not Active
                                </label>
                            </div>
                        </div>

                        <div class="form-group {{ readConfig('is_live') == 1 ? 'd-none' : '' }}" id="close_msg_div">
                            <label>Close Message</label>
                            <textarea placeholder="Close Message" class="form-control" rows="4" name="close_msg" cols="50">Website under maintenance&lt;h1&gt;Comming SOON&lt;/h1&gt;</textarea>
                        </div>
                    </div>
                </form>
            </div>
            @endcan
            @can('invoice_settings')
            <div class="tab-pane fade {{ @$_GET['active-tab'] == 'invoice-settings' ? 'active show' : '' }}"
                id="tabs-8" role="tabpanel" aria-labelledby="vert-tabs-8">
                <form action="{{ route('backend.admin.settings.website.invoice.update') }}" method="post">
                    @csrf
                    <div class="col-md-12 d-flex justify-content-between">
                        <h5>
                            <i class="fas fa-file-invoice"></i>
                            &nbsp;&nbsp;Invoice Settings
                        </h5>
                        <button type="submit" class="btn bg-gradient-primary">
                            <i class="fas fa-reply"></i>
                            &nbsp;Save Changes
                        </button>
                    </div>
                    <div class="form-group">
                        <label>Note to customer</label>
                        <input type="text" class="form-control" placeholder="Enter message for invoice" name="note_to_customer_invoice" value="{{ readConfig('note_to_customer_invoice') }}">
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="switch"><input type="hidden" name="is_show_logo_invoice" value="0">
                            <input onclick="updateCheckboxValue(this)" type="checkbox" {{ readConfig('is_show_logo_invoice') == 1 ? 'checked' : '' }} name="is_show_logo_invoice" id="is_show_logo_invoice" value="{{ readConfig('is_show_logo_invoice') == 1 ? 1 : '0' }}">
                            <span class="slider round"></span>
                        </label>
                        <label for="is_show_logo_invoice" class="mx-2">Logo</label>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="switch"><input type="hidden" name="is_show_site_invoice" value="0">
                            <input onclick="updateCheckboxValue(this)" type="checkbox" {{ readConfig('is_show_site_invoice') == 1 ? 'checked' : '' }} name="is_show_site_invoice" id="is_show_site_invoice" value="{{ readConfig('is_show_site_invoice') == 1 ? 1 : '0' }}">
                            <span class="slider round"></span>
                        </label>
                        <label for="is_show_site_invoice" class="mx-2">Site Name</label>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="switch"><input type="hidden" name="is_show_phone_invoice" value="0">
                            <input onclick="updateCheckboxValue(this)" type="checkbox" {{ readConfig('is_show_phone_invoice') == 1 ? 'checked' : '' }} name="is_show_phone_invoice" id="is_show_phone_invoice" value="{{ readConfig('is_show_phone_invoice') == 1 ? 1 : '0' }}">
                            <span class="slider round"></span>
                        </label>

                        <label for="is_show_phone_invoice" class="mx-2">Phone</label>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="switch"><input type="hidden" name="is_show_email_invoice" value="0">
                            <input onclick="updateCheckboxValue(this)" type="checkbox" {{ readConfig('is_show_email_invoice') == 1 ? 'checked' : '' }} name="is_show_email_invoice" id="is_show_email_invoice" value="{{ readConfig('is_show_email_invoice') == 1 ? 1 : '0' }}">
                            <span class="slider round"></span>
                        </label>
                        <label for="is_show_email_invoice" class="mx-2">Email</label>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="switch"><input type="hidden" name="is_show_address_invoice" value="0">
                            <input onclick="updateCheckboxValue(this)" type="checkbox" {{ readConfig('is_show_address_invoice') == 1 ? 'checked' : '' }} name="is_show_address_invoice" id="is_show_address_invoice" value="{{ readConfig('is_show_address_invoice') == 1 ? 1 : '0' }}">
                            <span class="slider round"></span>
                        </label>
                        <label for="is_show_address_invoice" class="mx-2">Address</label>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="switch"><input type="hidden" name="is_show_customer_invoice" value="0">
                            <input onclick="updateCheckboxValue(this)" type="checkbox" {{ readConfig('is_show_customer_invoice') == 1 ? 'checked' : '' }} name="is_show_customer_invoice" id="is_show_customer_invoice" value="{{ readConfig('is_show_customer_invoice') == 1 ? 1 : '0' }}">
                            <span class="slider round"></span>
                        </label>
                        <label for="is_show_customer_invoice" class="mx-2">Customer</label>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="switch"><input type="hidden" name="is_show_note_invoice" value="0">
                            <input onclick="updateCheckboxValue(this)" type="checkbox" {{ readConfig('is_show_note_invoice') == 1 ? 'checked' : '' }} name="is_show_note_invoice" id="is_show_note_invoice" value="{{ readConfig('is_show_note_invoice') == 1 ? 1 : '0' }}">
                            <span class="slider round"></span>
                        </label>
                        <label for="is_show_note_invoice" class="mx-2">Note to customer</label>
                    </div>
                    <div class="form-group">
                        <label class="">Pos Invoice Width</label>
                        <select name="receiptMaxwidth" class="form-control col-6">
                            <option value="300px" {{ readConfig('receiptMaxwidth') == '300px' ? 'selected' : '' }}>Small</option>
                            <option value="400px" {{ readConfig('receiptMaxwidth') == '400px' ? 'selected' : '' }}>Medium</option>
                            <option value="500px" {{ readConfig('receiptMaxwidth') == '500px' ? 'selected' : '' }}>Large</option>
                        </select>
                    </div>

                </form>
            </div>
            @endcan
            @can('system_update_settings')
            <div class="tab-pane fade {{ @$_GET['active-tab'] == 'system-update' ? 'active show' : '' }}"
                id="tabs-9" role="tabpanel" aria-labelledby="vert-tabs-9">

                <div class="col-md-12 d-flex justify-content-between">
                    <h5>
                        <i class="fas fa-sync-alt"></i>
                        &nbsp;&nbsp;System Update
                    </h5>
                </div>

                <div class="col-md-12">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-info-circle"></i> Current System Status
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Current Version:</strong>
                                    <span id="currentVersion" class="badge bg-secondary ml-1">Loading...</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Commit:</strong>
                                    <code id="currentCommit">...</code>
                                </div>
                                <div class="col-md-4">
                                    <strong>Status:</strong>
                                    <span id="updateStatus" class="badge bg-secondary ml-1">Checking...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 mt-3">
                    <div class="card card-outline card-warning">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-cloud-download-alt"></i> Check for Updates
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                Check if a newer version is available on the remote repository.
                            </p>
                            <button type="button" class="btn bg-gradient-info" id="checkUpdateBtn">
                                <i class="fas fa-search"></i> Check for Updates
                            </button>
                            <div id="checkResult" class="mt-3" style="display:none;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 mt-3">
                    <div class="card card-outline card-danger">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-download"></i> Apply Update
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <strong>Warning:</strong> This will pull the latest changes from git, update dependencies,
                                run migrations, and rebuild frontend assets. A rollback point will be saved automatically.
                            </div>
                            <button type="button" class="btn bg-gradient-danger" id="applyUpdateBtn">
                                <i class="fas fa-play-circle"></i> Pull &amp; Apply Update
                            </button>
                            <div id="applyResult" class="mt-3" style="display:none;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 mt-3" id="rollbackSection" style="display:none;">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-undo"></i> Rollback to Previous Version
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle mr-1"></i>
                                A rollback point was saved from a previous update.
                                You can revert the system back to that version if the current update is causing issues.
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Rollback Version:</strong>
                                    <span id="rollbackVersion" class="badge bg-secondary ml-1">...</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Commit:</strong>
                                    <code id="rollbackCommit">...</code>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Saved On:</strong>
                                    <span id="rollbackDate">...</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Saved By:</strong>
                                    <span id="rollbackUser">...</span>
                                </div>
                            </div>
                            <button type="button" class="btn bg-gradient-secondary" id="rollbackBtn">
                                <i class="fas fa-undo"></i> Rollback Now
                            </button>
                            <div id="rollbackResult" class="mt-3" style="display:none;"></div>
                        </div>
                    </div>
                </div>

            </div>
            @endcan
    </div>
</div>
</div>
@endsection

@push('script')
<script>
    $('input[type=radio][name=is_live]').on("change", function() {
        if (this.value == '0') {
            $("#close_msg_div").removeClass('d-none');
        } else {
            $("#close_msg_div").addClass('d-none');
        }
    });

    function updateCheckboxValue(checkbox) {
        checkbox.value = checkbox.checked ? '1' : '0';
    }

    // ── System Update JS ─────────────────────────────────────────
    $(function () {
        var checkUrl   = "{{ route('backend.admin.settings.website.check.update') }}";
        var applyUrl   = "{{ route('backend.admin.settings.website.apply.update') }}";
        var backupUrl  = "{{ route('backend.admin.settings.website.backup.info') }}";
        var rollbackUrl = "{{ route('backend.admin.settings.website.rollback.update') }}";
        var csrfToken  = "{{ csrf_token() }}";

        function loadCurrentVersion() {
            $.ajax({
                url: checkUrl,
                type: 'GET',
                success: function (res) {
                    $('#currentVersion').text('v' + res.local_version).removeClass('bg-secondary').addClass('bg-info');
                    $('#currentCommit').text(res.local_hash);
                    if (res.updatable) {
                        $('#updateStatus').text(res.behind + ' commit(s) behind').removeClass('bg-secondary').addClass('bg-warning');
                    } else {
                        $('#updateStatus').text('Up to date').removeClass('bg-secondary').addClass('bg-success');
                    }
                },
                error: function () {
                    $('#currentVersion').text('Error').removeClass('bg-secondary').addClass('bg-danger');
                    $('#updateStatus').text('Check failed').removeClass('bg-secondary').addClass('bg-danger');
                }
            });
        }

        function loadBackupInfo() {
            $.ajax({
                url: backupUrl,
                type: 'GET',
                success: function (res) {
                    if (res.available) {
                        $('#rollbackVersion').text('v' + res.version);
                        $('#rollbackCommit').text(res.commit ? res.commit.substring(0, 7) : 'N/A');
                        $('#rollbackDate').text(res.saved_at || 'N/A');
                        $('#rollbackUser').text(res.saved_by || 'N/A');
                        $('#rollbackSection').fadeIn();
                    } else {
                        $('#rollbackSection').hide();
                    }
                }
            });
        }

        loadCurrentVersion();
        loadBackupInfo();

        $('#checkUpdateBtn').on('click', function () {
            var btn = $(this).prop('disabled', true)
                        .html('<i class="fas fa-spinner fa-spin"></i> Checking...');
            $('#checkResult').hide();

            $.ajax({
                url: checkUrl,
                type: 'GET',
                success: function (res) {
                    var html = '';
                    if (res.updatable) {
                        html = '<div class="alert alert-warning mb-0">'
                             + '<i class="fas fa-arrow-circle-up mr-1"></i>'
                             + '<strong>Update available!</strong> '
                             + 'Remote has <strong>v' + res.remote_version + '</strong> '
                             + '(<code>' + res.remote_hash + '</code>) — '
                             + res.behind + ' commit(s) ahead of your local '
                             + '<strong>v' + res.local_version + '</strong>.'
                             + '</div>';
                    } else {
                        html = '<div class="alert alert-success mb-0">'
                             + '<i class="fas fa-check-circle mr-1"></i>'
                             + '<strong>Your system is up to date!</strong> '
                             + 'Running <strong>v' + res.local_version + '</strong> '
                             + '(<code>' + res.local_hash + '</code>) on branch <code>' + res.branch + '</code>.'
                             + '</div>';
                    }
                    $('#checkResult').html(html).fadeIn();
                    btn.prop('disabled', false)
                       .html('<i class="fas fa-search"></i> Check for Updates');
                },
                error: function () {
                    $('#checkResult').html(
                        '<div class="alert alert-danger mb-0">'
                        + '<i class="fas fa-exclamation-triangle mr-1"></i>'
                        + 'Failed to check for updates. Please ensure git is configured and accessible.'
                        + '</div>'
                    ).fadeIn();
                    btn.prop('disabled', false)
                       .html('<i class="fas fa-search"></i> Check for Updates');
                }
            });
        });

        $('#applyUpdateBtn').on('click', function () {
            Swal.fire({
                title: 'Apply System Update?',
                text: 'This will pull the latest code, install dependencies, run migrations, and rebuild assets. A rollback point will be saved.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, apply update!',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (result.isConfirmed) {
                    var btn = $('#applyUpdateBtn').prop('disabled', true)
                                .html('<i class="fas fa-spinner fa-spin"></i> Applying update...');
                    $('#applyResult').hide();

                    $.ajax({
                        url: applyUrl,
                        type: 'POST',
                        data: { _token: csrfToken },
                        success: function (res) {
                            var logHtml = '<div class="alert alert-success">'
                                        + '<i class="fas fa-check-circle mr-1"></i>'
                                        + '<strong>Update applied successfully!</strong> '
                                        + 'Now running <strong>v' + res.version + '</strong> '
                                        + '(<code>' + res.commit + '</code>).'
                                        + '</div>'
                                        + '<div class="card card-outline card-secondary mt-2">'
                                        + '<div class="card-header py-1">'
                                        + '<h6 class="card-title mb-0"><i class="fas fa-terminal mr-1"></i> Update Log</h6>'
                                        + '</div>'
                                        + '<div class="card-body p-2">'
                                        + '<pre style="max-height:400px;overflow:auto;background:#1a1a2e;color:#e0e0e0;padding:12px;border-radius:6px;font-size:12px;margin:0;">'
                                        + res.log.replace(/</g, '&lt;').replace(/>/g, '&gt;')
                                        + '</pre></div></div>';
                            $('#applyResult').html(logHtml).fadeIn();
                            btn.prop('disabled', false)
                               .html('<i class="fas fa-play-circle"></i> Pull & Apply Update');
                            loadCurrentVersion();
                            loadBackupInfo();
                            Swal.fire({
                                icon: 'success',
                                title: 'Update Complete',
                                html: 'System updated to <strong>v' + res.version + '</strong>.<br>A rollback point was saved at <strong>v' + res.rollback_version + '</strong>.',
                                timer: 6000,
                                showConfirmButton: false,
                            });
                        },
                        error: function (xhr) {
                            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Update failed. Check the error log.';
                            $('#applyResult').html(
                                '<div class="alert alert-danger mb-0">'
                                + '<i class="fas fa-exclamation-triangle mr-1"></i>'
                                + msg
                                + '</div>'
                            ).fadeIn();
                            btn.prop('disabled', false)
                               .html('<i class="fas fa-play-circle"></i> Pull & Apply Update');
                        }
                    });
                }
            });
        });

        $('#rollbackBtn').on('click', function () {
            Swal.fire({
                title: 'Rollback System?',
                html: 'This will revert the system to the previous version.<br><br>'
                    + '<strong class="text-danger">Current code, dependencies, and assets will be overwritten.</strong><br>'
                    + 'The latest database migration batch will also be rolled back.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, rollback now!',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (result.isConfirmed) {
                    var btn = $('#rollbackBtn').prop('disabled', true)
                                .html('<i class="fas fa-spinner fa-spin"></i> Rolling back...');
                    $('#rollbackResult').hide();

                    $.ajax({
                        url: rollbackUrl,
                        type: 'POST',
                        data: { _token: csrfToken },
                        success: function (res) {
                            var logHtml = '<div class="alert alert-success">'
                                        + '<i class="fas fa-check-circle mr-1"></i>'
                                        + '<strong>Rollback complete!</strong> '
                                        + 'System reverted to <strong>v' + res.version + '</strong> '
                                        + '(<code>' + res.commit + '</code>).'
                                        + '</div>'
                                        + '<div class="card card-outline card-secondary mt-2">'
                                        + '<div class="card-header py-1">'
                                        + '<h6 class="card-title mb-0"><i class="fas fa-terminal mr-1"></i> Rollback Log</h6>'
                                        + '</div>'
                                        + '<div class="card-body p-2">'
                                        + '<pre style="max-height:400px;overflow:auto;background:#1a1a2e;color:#e0e0e0;padding:12px;border-radius:6px;font-size:12px;margin:0;">'
                                        + res.log.replace(/</g, '&lt;').replace(/>/g, '&gt;')
                                        + '</pre></div></div>';
                            $('#rollbackResult').html(logHtml).fadeIn();
                            btn.prop('disabled', false)
                               .html('<i class="fas fa-undo"></i> Rollback Now');
                            loadCurrentVersion();
                            loadBackupInfo();
                            Swal.fire({
                                icon: 'success',
                                title: 'Rollback Complete',
                                text: 'System reverted to v' + res.version + '. Cache has been cleared.',
                                timer: 5000,
                                showConfirmButton: false,
                            });
                        },
                        error: function (xhr) {
                            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Rollback failed. Check the error log.';
                            $('#rollbackResult').html(
                                '<div class="alert alert-danger mb-0">'
                                + '<i class="fas fa-exclamation-triangle mr-1"></i>'
                                + msg
                                + '</div>'
                            ).fadeIn();
                            btn.prop('disabled', false)
                               .html('<i class="fas fa-undo"></i> Rollback Now');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush