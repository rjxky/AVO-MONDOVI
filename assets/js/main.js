/**
* Template Name: Green
* Updated: Mar 10 2023 with Bootstrap v5.2.3
* Template URL: https://bootstrapmade.com/green-free-one-page-bootstrap-template/
* Author: BootstrapMade.com
* License: https://bootstrapmade.com/license/
*/

(function() {
  "use strict";

  /**
   * Easy selector helper function
   */
  const select = (el, all = false) => {
    el = el.trim()
    if (all) {
      return [...document.querySelectorAll(el)]
    } else {
      return document.querySelector(el)
    }
  }

  /**
   * Easy event listener function
   */
  const on = (type, el, listener, all = false) => {
    let selectEl = select(el, all)
    if (selectEl) {
      if (all) {
        selectEl.forEach(e => e.addEventListener(type, listener))
      } else {
        selectEl.addEventListener(type, listener)
      }
    }
  }

  /**
   * Easy on scroll event listener 
   */
  const onscroll = (el, listener) => {
    el.addEventListener('scroll', listener)
  }

  /**
   * Navbar links active state on scroll
   */
  let navbarlinks = select('#navbar .scrollto', true)
  const navbarlinksActive = () => {
    let position = window.scrollY + 200
    navbarlinks.forEach(navbarlink => {
      if (!navbarlink.hash) return
      let section = select(navbarlink.hash)
      if (!section) return
      if (position >= section.offsetTop && position <= (section.offsetTop + section.offsetHeight)) {
        navbarlink.classList.add('active')
      } else {
        navbarlink.classList.remove('active')
      }
    })
  }
  
  window.addEventListener('load', function(){
    navbarlinksActive()

    let comuniInput = document.getElementById('comuni');
    let dropdown = document.getElementById('comunilist');
    if (dropdown && comuniInput) {
      fetch('forms/comuni.json')
        .then(response => {
          if (!response.ok) {
            throw new Error('Unable to load comuni.json')
          }
          return response.json()
        })
        .then(comuniData => {
          const comuniNames = comuniData
            .map(comune => comune.nome)
            .filter(Boolean)

          const renderComuniOptions = value => {
            dropdown.innerHTML = ''

            let search = value.trim().toLowerCase()
            if (search.length < 2) {
              return
            }

            comuniNames
              .filter(nome => nome.toLowerCase().startsWith(search))
              .slice(0, 12)
              .forEach(nome => {
                let option = document.createElement('option');
                option.text = nome;
                option.value = nome;
                dropdown.append(option);
              })
          }

          comuniInput.setAttribute('autocomplete', 'off')
          comuniInput.addEventListener('input', function() {
            renderComuniOptions(this.value)
          })
          comuniInput.addEventListener('focus', function() {
            renderComuniOptions(this.value)
          })
          comuniInput.addEventListener('blur', function() {
            window.setTimeout(() => {
              dropdown.innerHTML = ''
            }, 150)
          })

          if (comuniInput.value) {
            renderComuniOptions(comuniInput.value)
          }
        })
        .catch(error => console.warn('Lista comuni non disponibile:', error))
    }

    let instagramFeed = document.getElementById('instagram-feed-grid');
    let instagramStatus = document.getElementById('instagram-feed-status');
    if (instagramFeed && instagramStatus) {
      const rawFeedUrls = instagramFeed.dataset.feedUrl || 'https://instagram-feed-api-gamma.vercel.app/api/avo.mondovi,/api/instagram-feed.php';
      const feedUrls = rawFeedUrls
        .split(',')
        .map(url => url.trim())
        .filter(Boolean)
      const profileUrl = instagramFeed.dataset.profileUrl || 'https://www.instagram.com/avo.mondovi/';

      const formatFeedDate = isoDate => {
        if (!isoDate) {
          return ''
        }

        const date = new Date(isoDate)
        if (Number.isNaN(date.getTime())) {
          return ''
        }

        return new Intl.DateTimeFormat('it-IT', {
          day: '2-digit',
          month: 'short',
          year: 'numeric'
        }).format(date)
      }

      const createFeedCard = post => {
        const link = document.createElement('a')
        link.className = 'instagram-feed-card'
        link.href = post.url
        link.target = '_blank'
        link.rel = 'noreferrer noopener'
        link.setAttribute('aria-label', 'Apri il post Instagram')

        const media = document.createElement('div')
        media.className = 'instagram-feed-media'

        const image = document.createElement('img')
        image.src = post.image
        image.alt = post.caption ? post.caption.slice(0, 120) : 'Post Instagram AVO Mondovi'
        image.loading = 'lazy'
        media.appendChild(image)

        const badge = document.createElement('span')
        badge.className = 'instagram-feed-badge'
        badge.textContent = post.type === 'album' ? 'Album' : post.type === 'video' ? 'Video' : 'Post'
        media.appendChild(badge)

        const body = document.createElement('div')
        body.className = 'instagram-feed-body'

        const caption = document.createElement('p')
        caption.className = 'instagram-feed-caption'
        caption.textContent = post.caption || 'Apri il post per vedere il contenuto completo su Instagram.'

        const meta = document.createElement('div')
        meta.className = 'instagram-feed-meta'

        const handle = document.createElement('span')
        handle.textContent = '@avo.mondovi'

        const date = document.createElement('span')
        date.textContent = formatFeedDate(post.date_iso)

        meta.append(handle, date)
        body.append(caption, meta)
        link.append(media, body)

        return link
      }

      const normalizeExternalPost = post => ({
        url: post.slug ? 'https://www.instagram.com/p/' + post.slug + '/' : '',
        image: post.image || '',
        caption: post.description || '',
        date_iso: post.takenAt || null,
        type: post.type === 'carousel_album' ? 'album' : post.type === 'video' ? 'video' : 'photo'
      })

      const normalizeFeedData = data => {
        if (Array.isArray(data)) {
          return data
            .map(normalizeExternalPost)
            .filter(post => post.url && post.image)
            .slice(0, 10)
        }

        if (data && data.ok && Array.isArray(data.posts)) {
          return data.posts
            .filter(post => post && post.url && post.image)
            .slice(0, 10)
        }

        return []
      }

      const loadFeed = urls => {
        if (!urls.length) {
          return Promise.reject(new Error('Nessuna sorgente feed disponibile'))
        }

        const [currentUrl, ...nextUrls] = urls
        return fetch(currentUrl)
          .then(response => {
            if (!response.ok) {
              throw new Error('Feed Instagram non disponibile')
            }
            return response.json()
          })
          .then(data => {
            const posts = normalizeFeedData(data)
            if (!posts.length) {
              throw new Error('Nessun post disponibile')
            }
            return posts
          })
          .catch(error => {
            if (!nextUrls.length) {
              throw error
            }
            return loadFeed(nextUrls)
          })
      }

      loadFeed(feedUrls)
        .then(posts => {
          instagramFeed.innerHTML = ''
          posts.forEach(post => {
            instagramFeed.appendChild(createFeedCard(post))
          })

          instagramStatus.textContent = 'Ultimi 10 post pubblicati su Instagram.'
        })
        .catch(error => {
          console.warn('Feed Instagram non disponibile:', error)
          instagramFeed.innerHTML = ''
          instagramStatus.innerHTML = 'Impossibile caricare il feed in questo momento. <a href="' + profileUrl + '" target="_blank" rel="noreferrer noopener">Apri il profilo Instagram</a>.'
        })
    }
  })
  onscroll(document, navbarlinksActive)
          /*
          comuniData.forEach(comune => {
            let option = document.createElement('option');
            option.text = comune.nome;
            option.value = comune.nome;
            dropdown.append(option);
          })
          */

  /**
   * Scrolls to an element with header offset
   */
  const scrollto = (el) => {
    let header = select('#header')
    let offset = header.offsetHeight

    if (!header.classList.contains('header-scrolled')) {
      offset -= 16
    }

    let elementPos = select(el).offsetTop
    window.scrollTo({
      top: elementPos - offset,
      behavior: 'smooth'
    })
  }

  /**
   * Header fixed top on scroll
   */
  let selectHeader = select('#header')
  if (selectHeader) {
    let headerOffset = selectHeader.offsetTop
    let nextElement = selectHeader.nextElementSibling
    const headerFixed = () => {
      if ((headerOffset - window.scrollY) <= 0) {
        selectHeader.classList.add('fixed-top')
        nextElement.classList.add('scrolled-offset')
      } else {
        selectHeader.classList.remove('fixed-top')
        nextElement.classList.remove('scrolled-offset')
      }
    }
    window.addEventListener('load', headerFixed)
    onscroll(document, headerFixed)
  }

  /**
   * Back to top button
   */
  let backtotop = select('.back-to-top')
  if (backtotop) {
    const toggleBacktotop = () => {
      if (window.scrollY > 100) {
        backtotop.classList.add('active')
      } else {
        backtotop.classList.remove('active')
      }
    }
    window.addEventListener('load', toggleBacktotop)
    onscroll(document, toggleBacktotop)
  }

  /**
   * Mobile nav toggle
   */
  on('click', '.mobile-nav-toggle', function(e) {
    select('#navbar').classList.toggle('navbar-mobile')
    this.classList.toggle('bi-list')
    this.classList.toggle('bi-x')
  })

  /**
   * Mobile nav dropdowns activate
   */
  on('click', '.navbar .dropdown > a', function(e) {
    if (select('#navbar').classList.contains('navbar-mobile')) {
      e.preventDefault()
      this.nextElementSibling.classList.toggle('dropdown-active')
    }
  }, true)

  /**
   * Scrool with ofset on links with a class name .scrollto
   */
  on('click', '.scrollto', function(e) {
    if (select(this.hash)) {
      e.preventDefault()

      let navbar = select('#navbar')
      if (navbar.classList.contains('navbar-mobile')) {
        navbar.classList.remove('navbar-mobile')
        let navbarToggle = select('.mobile-nav-toggle')
        navbarToggle.classList.toggle('bi-list')
        navbarToggle.classList.toggle('bi-x')
      }
      scrollto(this.hash)
    }
  }, true)

  /**
   * Scroll with ofset on page load with hash links in the url
   */
  window.addEventListener('load', () => {
    if (window.location.hash) {
      if (select(window.location.hash)) {
        scrollto(window.location.hash)
      }
    }
  });

  const inputHandler = function(e) {
    if (e.target.value.length == 3) {
      phoneNo.value = phoneNo.value + "-" ;
    }
    if (e.target.value.length == 7) {
      phoneNo.value = phoneNo.value + "-" ;
    }
  }

  const phoneNo = document.getElementById('phoneNo');
  if (phoneNo) {
    phoneNo.addEventListener('input', inputHandler);
    phoneNo.addEventListener('propertychange', inputHandler);
  }

  

  /**
   * Hero carousel indicators
   */
  let heroCarouselIndicators = select("#hero-carousel-indicators")
  let heroCarouselItems = select('#heroCarousel .carousel-item', true)

  if (heroCarouselIndicators && heroCarouselItems.length && !heroCarouselIndicators.children.length) {
    heroCarouselItems.forEach((item, index) => {
      (index === 0) ?
      heroCarouselIndicators.innerHTML += "<button type='button' data-bs-target='#heroCarousel' data-bs-slide-to='" + index + "' class='active' aria-current='true' aria-label='Slide " + (index + 1) + "'></button>":
        heroCarouselIndicators.innerHTML += "<button type='button' data-bs-target='#heroCarousel' data-bs-slide-to='" + index + "' aria-label='Slide " + (index + 1) + "'></button>"
    });
  }

  /**
   * Clients Slider
   */
  if (typeof Swiper !== 'undefined' && select('.clients-slider')) {
    new Swiper('.clients-slider', {
      speed: 400,
      loop: true,
      autoplay: {
        delay: 5000,
        disableOnInteraction: false
      },
      slidesPerView: 'auto',
      pagination: {
        el: '.swiper-pagination',
        type: 'bullets',
        clickable: true
      },
      breakpoints: {
        320: {
          slidesPerView: 2,
          spaceBetween: 40
        },
        480: {
          slidesPerView: 3,
          spaceBetween: 60
        },
        640: {
          slidesPerView: 4,
          spaceBetween: 80
        },
        992: {
          slidesPerView: 6,
          spaceBetween: 120
        }
      }
    });
  }

  /**
   * Porfolio isotope and filter
   */
  window.addEventListener('load', () => {
    let portfolioContainer = select('.portfolio-container');
    if (portfolioContainer && typeof Isotope !== 'undefined') {
      let portfolioIsotope = new Isotope(portfolioContainer, {
        itemSelector: '.portfolio-item'
      });

      let portfolioFilters = select('#portfolio-flters li', true);

      on('click', '#portfolio-flters li', function(e) {
        e.preventDefault();
        portfolioFilters.forEach(function(el) {
          el.classList.remove('filter-active');
        });
        this.classList.add('filter-active');

        portfolioIsotope.arrange({
          filter: this.getAttribute('data-filter')
        });

      }, true);
    }

  });

  /**
   * Initiate portfolio lightbox 
   */
  if (typeof GLightbox !== 'undefined') {
    GLightbox({
      selector: '.portfolio-lightbox'
    });
  }

  /**
   * Portfolio details slider
   */
  if (typeof Swiper !== 'undefined' && select('.portfolio-details-slider')) {
    new Swiper('.portfolio-details-slider', {
      speed: 400,
      loop: true,
      autoplay: {
        delay: 5000,
        disableOnInteraction: false
      },
      pagination: {
        el: '.swiper-pagination',
        type: 'bullets',
        clickable: true
      }
    });
  }

})()
