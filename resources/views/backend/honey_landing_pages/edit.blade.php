@extends('backend.app')

@push('css')
<style>
  .vertical-tabs-container {
    display: flex;
    gap: 20px;
    margin-top: 20px;
  }
  .tab-nav {
    width: 250px;
    flex-shrink: 0;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    height: fit-content;
  }
  .tab-content-area {
    flex: 1;
    min-width: 0;
  }
  .nav-pills .nav-link {
    border-radius: 8px;
    margin-bottom: 8px;
    padding: 12px 16px;
    color: #495057;
    background: white;
    border: 1px solid #dee2e6;
    text-align: left;
  }
  .nav-pills .nav-link:hover {
    background: #e9ecef;
  }
  .nav-pills .nav-link.active {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
  }
  .tab-pane {
    display: none;
  }
  .tab-pane.active {
    display: block;
  }
  .form-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #dee2e6;
  }
  .dynamic-item {
    border: 1px solid #dee2e6;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 8px;
    background: #f8f9fa;
    position: relative;
  }
  .remove-item {
    position: absolute;
    top: 10px;
    right: 10px;
  }
  .image-preview {
    max-width: 150px;
    max-height: 150px;
    margin-top: 10px;
    border-radius: 8px;
  }
</style>
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right">
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item"><a href="javascript:void(0);">SIS</a></li>
          <li class="breadcrumb-item"><a href="javascript:void(0);">CRM</a></li>
          <li class="breadcrumb-item active">Honey Landing Page Edit</li>
        </ol>
      </div>
      <h4 class="page-title">Edit Honey Landing Page: {{ $page->title }}</h4>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="vertical-tabs-container">
          <!-- Left Sidebar Navigation -->
          <div class="tab-nav">
            <ul class="nav nav-pills flex-column" id="v-tabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="pill" href="#hero" role="tab">Hero Section</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="pill" href="#welcome" role="tab">Welcome</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="pill" href="#description" role="tab">Description</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="pill" href="#why_buy" role="tab">Why Buy Our Honey</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="pill" href="#why_eat_honey" role="tab">Why Eat Honey</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="pill" href="#reviews" role="tab">Reviews</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="pill" href="#faq" role="tab">FAQ</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="pill" href="#product" role="tab">Product/Price</a>
              </li>
            </ul>
          </div>

          <!-- Right Content Area -->
          <div class="tab-content-area">
            <div class="tab-content" id="v-tabsContent">
              
              <!-- Hero Section -->
              <div class="tab-pane fade show active" id="hero" role="tabpanel">
                <div class="form-section">
                  <h5 class="mb-4">Hero Section</h5>
                  <form id="ajax_form" action="{{ route('admin.honey_landing_pages.update_section', $page->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="section" value="hero">
                    
                    <div class="mb-3">
                      <label class="form-label">Heading</label>
                      <input type="text" name="content[hero][heading]" class="form-control" 
                             value="{{ $page->content['hero']['heading'] ?? '' }}" placeholder="Enter hero heading">
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Questions</label>
                      <div id="questions-container">
                        @if(isset($page->content['hero']['questions']) && is_array($page->content['hero']['questions']))
                          @foreach($page->content['hero']['questions'] as $index => $question)
                            <div class="dynamic-item question-item">
                              <input type="text" name="content[hero][questions][]" class="form-control mb-2" value="{{ $question }}" placeholder="Enter question">
                              <button type="button" class="btn btn-sm btn-danger remove-item">Remove</button>
                            </div>
                          @endforeach
                        @endif
                      </div>
                      <button type="button" class="btn btn-sm btn-primary add-question">Add Question</button>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Hero Section</button>
                  </form>
                </div>
              </div>

              <!-- Welcome Section -->
              <div class="tab-pane fade" id="welcome" role="tabpanel">
                <div class="form-section">
                  <h5 class="mb-4">Welcome Section</h5>
                  <form id="ajax_form" action="{{ route('admin.honey_landing_pages.update_section', $page->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="section" value="welcome">
                    
                    <div class="mb-3">
                      <label class="form-label">Heading</label>
                      <input type="text" name="content[welcome][heading]" class="form-control" 
                             value="{{ $page->content['welcome']['heading'] ?? '' }}" placeholder="Enter welcome heading">
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Message</label>
                      <textarea name="content[welcome][message]" class="form-control" rows="4" 
                                placeholder="Enter welcome message">{{ $page->content['welcome']['message'] ?? '' }}</textarea>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Logo</label>
                      <input type="file" name="logo" class="form-control">
                      @if(isset($page->content['welcome']['logo']) && $page->content['welcome']['logo'])
                        <img src="{{ asset($page->content['welcome']['logo']) }}" alt="Current logo" class="image-preview">
                      @endif
                    </div>

                    <button type="submit" class="btn btn-primary">Save Welcome Section</button>
                  </form>
                </div>
              </div>

              <!-- Description Section -->
              <div class="tab-pane fade" id="description" role="tabpanel">
                <div class="form-section">
                  <h5 class="mb-4">Description Section</h5>
                  <form id="ajax_form" action="{{ route('admin.honey_landing_pages.update_section', $page->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="section" value="description">
                    
                    <div class="mb-3">
                      <label class="form-label">Description</label>
                      <textarea name="content[description]" class="form-control" rows="6" 
                                placeholder="Enter description">{{ $page->content['description'] ?? '' }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Description Section</button>
                  </form>
                </div>
              </div>

              <!-- Why Buy Section -->
              <div class="tab-pane fade" id="why_buy" role="tabpanel">
                <div class="form-section">
                  <h5 class="mb-4">Why Buy Our Honey Section</h5>
                  <form id="ajax_form" action="{{ route('admin.honey_landing_pages.update_section', $page->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="section" value="why_buy">
                    
                    <div class="mb-3">
                      <label class="form-label">Center Image</label>
                      <input type="file" name="center_image" class="form-control">
                      @if(isset($page->content['why_buy']['center_image']) && $page->content['why_buy']['center_image'])
                        <img src="{{ asset($page->content['why_buy']['center_image']) }}" alt="Current image" class="image-preview">
                      @endif
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Cards</label>
                      <div id="why-buy-cards-container">
                        @if(isset($page->content['why_buy']['cards']) && is_array($page->content['why_buy']['cards']))
                          @foreach($page->content['why_buy']['cards'] as $index => $card)
                            <div class="dynamic-item card-item">
                              <input type="hidden" name="content[why_buy][cards][{{ $index }}][icon]" value="{{ $card['icon'] ?? '' }}">
                              <div class="mb-2">
                                <label>Icon</label>
                                <input type="file" name="card_icon_{{ $index }}" class="form-control">
                                @if(isset($card['icon']) && $card['icon'])
                                  <img src="{{ asset($card['icon']) }}" alt="Icon" class="image-preview">
                                @endif
                              </div>
                              <div class="mb-2">
                                <label>Heading</label>
                                <input type="text" name="content[why_buy][cards][{{ $index }}][heading]" class="form-control" value="{{ $card['heading'] ?? '' }}">
                              </div>
                              <div class="mb-2">
                                <label>Description</label>
                                <textarea name="content[why_buy][cards][{{ $index }}][description]" class="form-control" rows="3">{{ $card['description'] ?? '' }}</textarea>
                              </div>
                              <button type="button" class="btn btn-sm btn-danger remove-item">Remove</button>
                            </div>
                          @endforeach
                        @endif
                      </div>
                      <button type="button" class="btn btn-sm btn-primary add-why-buy-card">Add Card</button>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Why Buy Section</button>
                  </form>
                </div>
              </div>

              <!-- Why Eat Honey Section -->
              <div class="tab-pane fade" id="why_eat_honey" role="tabpanel">
                <div class="form-section">
                  <h5 class="mb-4">Why Eat Honey Section</h5>
                  <form id="ajax_form" action="{{ route('admin.honey_landing_pages.update_section', $page->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="section" value="why_eat_honey">
                    
                    <div class="mb-3">
                      <label class="form-label">Cards</label>
                      <div id="why-eat-cards-container">
                        @if(isset($page->content['why_eat_honey']['cards']) && is_array($page->content['why_eat_honey']['cards']))
                          @foreach($page->content['why_eat_honey']['cards'] as $index => $card)
                            <div class="dynamic-item eat-card-item">
                              <input type="hidden" name="content[why_eat_honey][cards][{{ $index }}][icon]" value="{{ $card['icon'] ?? '' }}">
                              <div class="mb-2">
                                <label>Icon</label>
                                <input type="file" name="eat_card_icon_{{ $index }}" class="form-control">
                                @if(isset($card['icon']) && $card['icon'])
                                  <img src="{{ asset($card['icon']) }}" alt="Icon" class="image-preview">
                                @endif
                              </div>
                              <div class="mb-2">
                                <label>Title</label>
                                <input type="text" name="content[why_eat_honey][cards][{{ $index }}][title]" class="form-control" value="{{ $card['title'] ?? '' }}">
                              </div>
                              <div class="mb-2">
                                <label>Description</label>
                                <textarea name="content[why_eat_honey][cards][{{ $index }}][description]" class="form-control" rows="3">{{ $card['description'] ?? '' }}</textarea>
                              </div>
                              <button type="button" class="btn btn-sm btn-danger remove-item">Remove</button>
                            </div>
                          @endforeach
                        @endif
                      </div>
                      <button type="button" class="btn btn-sm btn-primary add-why-eat-card">Add Card</button>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Why Eat Honey Section</button>
                  </form>
                </div>
              </div>

              <!-- Reviews Section -->
              <div class="tab-pane fade" id="reviews" role="tabpanel">
                <div class="form-section">
                  <h5 class="mb-4">Reviews Section</h5>
                  <form id="ajax_form" action="{{ route('admin.honey_landing_pages.update_section', $page->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="section" value="reviews">
                    
                    <div class="mb-3">
                      <label class="form-label">Review Type</label>
                      <select name="content[reviews][type]" class="form-select" id="review-type">
                        <option value="screenshot" {{ ($page->content['reviews']['type'] ?? '') == 'screenshot' ? 'selected' : '' }}>Screenshot</option>
                        <option value="review" {{ ($page->content['reviews']['type'] ?? 'review') == 'review' ? 'selected' : '' }}>Review</option>
                      </select>
                    </div>

                    <div class="mb-3" id="screenshot-field" style="display: {{ ($page->content['reviews']['type'] ?? 'review') == 'screenshot' ? 'block' : 'none' }};">
                      <label class="form-label">Screenshot</label>
                      <input type="file" name="screenshot" class="form-control">
                      @if(isset($page->content['reviews']['screenshot']) && $page->content['reviews']['screenshot'])
                        <img src="{{ asset($page->content['reviews']['screenshot']) }}" alt="Screenshot" class="image-preview">
                      @endif
                    </div>

                    <div class="mb-3" id="review-cards-field" style="display: {{ ($page->content['reviews']['type'] ?? 'review') == 'review' ? 'block' : 'none' }};">
                      <label class="form-label">Review Cards</label>
                      <div id="review-cards-container">
                        @if(isset($page->content['reviews']['review_cards']) && is_array($page->content['reviews']['review_cards']))
                          @foreach($page->content['reviews']['review_cards'] as $index => $card)
                            <div class="dynamic-item review-card-item">
                              <input type="hidden" name="content[reviews][review_cards][{{ $index }}][avatar]" value="{{ $card['avatar'] ?? '' }}">
                              <div class="mb-2">
                                <label>Avatar</label>
                                <input type="file" name="review_avatar_{{ $index }}" class="form-control">
                                @if(isset($card['avatar']) && $card['avatar'])
                                  <img src="{{ asset($card['avatar']) }}" alt="Avatar" class="image-preview">
                                @endif
                              </div>
                              <div class="mb-2">
                                <label>Name</label>
                                <input type="text" name="content[reviews][review_cards][{{ $index }}][name]" class="form-control" value="{{ $card['name'] ?? '' }}">
                              </div>
                              <div class="mb-2">
                                <label>Rating (1-5)</label>
                                <input type="number" name="content[reviews][review_cards][{{ $index }}][rating]" class="form-control" min="1" max="5" value="{{ $card['rating'] ?? 5 }}">
                              </div>
                              <div class="mb-2">
                                <label>Details</label>
                                <textarea name="content[reviews][review_cards][{{ $index }}][details]" class="form-control" rows="3">{{ $card['details'] ?? '' }}</textarea>
                              </div>
                              <button type="button" class="btn btn-sm btn-danger remove-item">Remove</button>
                            </div>
                          @endforeach
                        @endif
                      </div>
                      <button type="button" class="btn btn-sm btn-primary add-review-card">Add Review Card</button>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Reviews Section</button>
                  </form>
                </div>
              </div>

              <!-- FAQ Section -->
              <div class="tab-pane fade" id="faq" role="tabpanel">
                <div class="form-section">
                  <h5 class="mb-4">FAQ Section</h5>
                  <form id="ajax_form" action="{{ route('admin.honey_landing_pages.update_section', $page->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="section" value="faq">
                    
                    <div class="mb-3">
                      <label class="form-label">FAQ Items</label>
                      <div id="faq-items-container">
                        @if(isset($page->content['faq']['items']) && is_array($page->content['faq']['items']))
                          @foreach($page->content['faq']['items'] as $index => $item)
                            <div class="dynamic-item faq-item">
                              <div class="mb-2">
                                <label>Question</label>
                                <input type="text" name="content[faq][items][{{ $index }}][question]" class="form-control" value="{{ $item['question'] ?? '' }}">
                              </div>
                              <div class="mb-2">
                                <label>Answer</label>
                                <textarea name="content[faq][items][{{ $index }}][answer]" class="form-control" rows="3">{{ $item['answer'] ?? '' }}</textarea>
                              </div>
                              <button type="button" class="btn btn-sm btn-danger remove-item">Remove</button>
                            </div>
                          @endforeach
                        @endif
                      </div>
                      <button type="button" class="btn btn-sm btn-primary add-faq-item">Add FAQ Item</button>
                    </div>

                    <button type="submit" class="btn btn-primary">Save FAQ Section</button>
                  </form>
                </div>
              </div>

              <!-- Product/Price Section -->
              <div class="tab-pane fade" id="product" role="tabpanel">
                <div class="form-section">
                  <h5 class="mb-4">Product/Price Section</h5>
                  <form id="ajax_form" action="{{ route('admin.honey_landing_pages.update_section', $page->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="section" value="product">
                    
                    <div class="mb-3">
                      <label class="form-label">Product Type</label>
                      <select name="content[product][type]" class="form-select" id="product-type">
                        <option value="existing" {{ ($page->content['product']['type'] ?? 'static') == 'existing' ? 'selected' : '' }}>Existing Product</option>
                        <option value="static" {{ ($page->content['product']['type'] ?? 'static') == 'static' ? 'selected' : '' }}>Static Product</option>
                      </select>
                    </div>

                    <div class="mb-3" id="existing-product-field" style="display: {{ ($page->content['product']['type'] ?? 'static') == 'existing' ? 'block' : 'none' }};">
                      <label class="form-label">Select Product</label>
                      <select name="content[product][product_id]" class="form-select">
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                          <option value="{{ $product->id }}" {{ ($page->content['product']['product_id'] ?? '') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                          </option>
                        @endforeach
                      </select>
                    </div>

                    <div id="static-product-fields" style="display: {{ ($page->content['product']['type'] ?? 'static') == 'static' ? 'block' : 'none' }};">
                      <div class="mb-3">
                        <label class="form-label">Product Title</label>
                        <input type="text" name="content[product][title]" class="form-control" 
                               value="{{ $page->content['product']['title'] ?? '' }}" placeholder="Enter product title">
                      </div>

                      <div class="mb-3">
                        <label class="form-label">Product Image</label>
                        <input type="file" name="product_image" class="form-control">
                        @if(isset($page->content['product']['image']) && $page->content['product']['image'])
                          <img src="{{ asset($page->content['product']['image']) }}" alt="Product image" class="image-preview">
                        @endif
                      </div>

                      <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="text" name="content[product][quantity]" class="form-control" 
                               value="{{ $page->content['product']['quantity'] ?? '' }}" placeholder="e.g., ৪ গ্রাম × ৫০টি স্যাচেট">
                      </div>

                      <div class="mb-3">
                        <label class="form-label">Regular Price</label>
                        <input type="number" name="content[product][regular_price]" class="form-control" 
                               value="{{ $page->content['product']['regular_price'] ?? '' }}" placeholder="Enter regular price">
                      </div>

                      <div class="mb-3">
                        <label class="form-label">Offer Price</label>
                        <input type="number" name="content[product][offer_price]" class="form-control" 
                               value="{{ $page->content['product']['offer_price'] ?? '' }}" placeholder="Enter offer price">
                      </div>

                      <div class="mb-3">
                        <label class="form-label">Short Description</label>
                        <textarea name="content[product][short_description]" class="form-control" rows="4" 
                                  placeholder="Enter short description">{{ $page->content['product']['short_description'] ?? '' }}</textarea>
                      </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Product/Price Section</button>
                  </form>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('js')
<script>
$(document).ready(function() {
  // Tab persistence
  var activeTab = localStorage.getItem('honey_active_tab');
  if (activeTab) {
    $('.nav-link[href="' + activeTab + '"]').tab('show');
  }
  
  $('.nav-link').on('shown.bs.tab', function (e) {
    localStorage.setItem('honey_active_tab', $(e.target).attr('href'));
  });

  // Review type toggle
  $('#review-type').on('change', function() {
    if ($(this).val() == 'screenshot') {
      $('#screenshot-field').show();
      $('#review-cards-field').hide();
    } else {
      $('#screenshot-field').hide();
      $('#review-cards-field').show();
    }
  });

  // Product type toggle
  $('#product-type').on('change', function() {
    if ($(this).val() == 'existing') {
      $('#existing-product-field').show();
      $('#static-product-fields').hide();
    } else {
      $('#existing-product-field').hide();
      $('#static-product-fields').show();
    }
  });

  // Add Question
  var questionIndex = {{ isset($page->content['hero']['questions']) ? count($page->content['hero']['questions']) : 0 }};
  $(document).on('click', '.add-question', function() {
    var html = '<div class="dynamic-item question-item">' +
               '<input type="text" name="content[hero][questions][]" class="form-control mb-2" placeholder="Enter question">' +
               '<button type="button" class="btn btn-sm btn-danger remove-item">Remove</button>' +
               '</div>';
    $('#questions-container').append(html);
  });

  // Add Why Buy Card
  var whyBuyCardIndex = {{ isset($page->content['why_buy']['cards']) ? count($page->content['why_buy']['cards']) : 0 }};
  $(document).on('click', '.add-why-buy-card', function() {
    var html = '<div class="dynamic-item card-item">' +
               '<input type="hidden" name="content[why_buy][cards][' + whyBuyCardIndex + '][icon]" value="">' +
               '<div class="mb-2"><label>Icon</label><input type="file" name="card_icon_' + whyBuyCardIndex + '" class="form-control"></div>' +
               '<div class="mb-2"><label>Heading</label><input type="text" name="content[why_buy][cards][' + whyBuyCardIndex + '][heading]" class="form-control" placeholder="Enter heading"></div>' +
               '<div class="mb-2"><label>Description</label><textarea name="content[why_buy][cards][' + whyBuyCardIndex + '][description]" class="form-control" rows="3" placeholder="Enter description"></textarea></div>' +
               '<button type="button" class="btn btn-sm btn-danger remove-item">Remove</button>' +
               '</div>';
    $('#why-buy-cards-container').append(html);
    whyBuyCardIndex++;
  });

  // Add Why Eat Card
  var whyEatCardIndex = {{ isset($page->content['why_eat_honey']['cards']) ? count($page->content['why_eat_honey']['cards']) : 0 }};
  $(document).on('click', '.add-why-eat-card', function() {
    var html = '<div class="dynamic-item eat-card-item">' +
               '<input type="hidden" name="content[why_eat_honey][cards][' + whyEatCardIndex + '][icon]" value="">' +
               '<div class="mb-2"><label>Icon</label><input type="file" name="eat_card_icon_' + whyEatCardIndex + '" class="form-control"></div>' +
               '<div class="mb-2"><label>Title</label><input type="text" name="content[why_eat_honey][cards][' + whyEatCardIndex + '][title]" class="form-control"></div>' +
               '<div class="mb-2"><label>Description</label><textarea name="content[why_eat_honey][cards][' + whyEatCardIndex + '][description]" class="form-control" rows="3"></textarea></div>' +
               '<button type="button" class="btn btn-sm btn-danger remove-item">Remove</button>' +
               '</div>';
    $('#why-eat-cards-container').append(html);
    whyEatCardIndex++;
  });

  // Add Review Card
  var reviewCardIndex = {{ isset($page->content['reviews']['review_cards']) ? count($page->content['reviews']['review_cards']) : 0 }};
  $(document).on('click', '.add-review-card', function() {
    var html = '<div class="dynamic-item review-card-item">' +
               '<input type="hidden" name="content[reviews][review_cards][' + reviewCardIndex + '][avatar]" value="">' +
               '<div class="mb-2"><label>Avatar</label><input type="file" name="review_avatar_' + reviewCardIndex + '" class="form-control"></div>' +
               '<div class="mb-2"><label>Name</label><input type="text" name="content[reviews][review_cards][' + reviewCardIndex + '][name]" class="form-control"></div>' +
               '<div class="mb-2"><label>Rating (1-5)</label><input type="number" name="content[reviews][review_cards][' + reviewCardIndex + '][rating]" class="form-control" min="1" max="5" value="5"></div>' +
               '<div class="mb-2"><label>Details</label><textarea name="content[reviews][review_cards][' + reviewCardIndex + '][details]" class="form-control" rows="3"></textarea></div>' +
               '<button type="button" class="btn btn-sm btn-danger remove-item">Remove</button>' +
               '</div>';
    $('#review-cards-container').append(html);
    reviewCardIndex++;
  });

  // Add FAQ Item
  var faqIndex = {{ isset($page->content['faq']['items']) ? count($page->content['faq']['items']) : 0 }};
  $(document).on('click', '.add-faq-item', function() {
    var html = '<div class="dynamic-item faq-item">' +
               '<div class="mb-2"><label>Question</label><input type="text" name="content[faq][items][' + faqIndex + '][question]" class="form-control"></div>' +
               '<div class="mb-2"><label>Answer</label><textarea name="content[faq][items][' + faqIndex + '][answer]" class="form-control" rows="3"></textarea></div>' +
               '<button type="button" class="btn btn-sm btn-danger remove-item">Remove</button>' +
               '</div>';
    $('#faq-items-container').append(html);
    faqIndex++;
  });

  // Remove item
  $(document).on('click', '.remove-item', function() {
    $(this).closest('.dynamic-item').remove();
  });
});
</script>
@endpush
@endsection
