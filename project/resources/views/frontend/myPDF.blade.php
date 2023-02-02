<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <!-- CSS files -->
    {{-- <link rel="shortcut icon" href="{{getPhoto($gs->favicon)}}"> --}}
    {{-- <link rel="stylesheet" href="{{public_path('assets/admin/')}}css/font-awsome.min.css"> --}}

    {{-- <link href="{{URL::asset('assets/user/')}}/css/tabler.min.css" rel="stylesheet"/>
    <link href="{{URL::asset('assets/user/')}}/css/tabler-flags.min.css" rel="stylesheet"/>
    <link href="{{URL::asset('assets/user/')}}/css/tabler-payments.min.css" rel="stylesheet"/>
    <link href="{{URL::asset('assets/user/')}}/css/tabler-vendors.min.css" rel="stylesheet"/>
    <link href="{{URL::asset('assets/user/')}}/css/custom.css" rel="stylesheet"/>
    <link href="{{URL::asset('assets/user/')}}/css/demo.min.css" rel="stylesheet"/>


    <link rel="stylesheet" type="text/css" href="{{URL::asset('assets/user/')}}/css/bootstrap-4.3.1.css">
    <script type="text/javascript" src="{{URL::asset('assets/user/')}}/js/jquery-1.12.4.min.js"></script>
    <link type="text/css" href="{{URL::asset('assets/user/')}}/css/jquery-ui.css" rel="stylesheet">
    <script type="text/javascript" src="{{URL::asset('assets/user/')}}/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="{{URL::asset('assets/user/')}}/js/jquery.signature.js"></script>
    <link rel="stylesheet" type="text/css" href="{{URL::asset('assets/user/')}}/css/jquery.signature.css"> --}}
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>

        #document-des {
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        .document-logo {
            height: 8rem;
            width: auto;
        }
    </style>
</head>
<body>
    <div>
        <div class="text-center row text-wrap text-center">
            <div class="mt-3">
                    <img src="data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxMSEhUTExMWFhUXGB8aGBgYGRoXGxkbGBkYGBkYHRgYHSggGBomHRcYITEhJSkrLi4uGB8zODMtNygtLisBCgoKDg0OGxAQGy0lICYvLy0uLS0vLS0tLS0tLS0tLS8tLS0tLS0tLS8tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAKcBLQMBIgACEQEDEQH/xAAbAAABBQEBAAAAAAAAAAAAAAAEAAIDBQYBB//EAD8QAAIBAwMCBAQEAwcDAwUAAAECEQADIQQSMUFRBRMiYQZxgZEyQqGxUsHRFBUjYoLh8FNy8TNDkgcWg6LC/8QAGgEAAgMBAQAAAAAAAAAAAAAAAwQBAgUABv/EADIRAAEDAgMHAwQDAAIDAAAAAAEAAhEDIRIxQQRRYXGBkfChsdETIjLBBeHxM0IUFWL/2gAMAwEAAhEDEQA/APH5xTV4rnSmlsUJFXGNRzSmuTV1RdNNJpztUVcFKfup1m1uPtXdPZ3HOBRQTJjgCql0WCu1s3T1Efh4p1k+qe1PtL6ePkf5Vy2CuIz1oBKYGim1c7MGQeRUYujgiuG5uKiIzRT3WRp6DuBzVMrIhg3GSrt5zHBonT3T0MN7VHcaZkVEw6DmiRKG0kGQf0pzcAQ7lkkzPtRLa2yybF3Dt7/WgLzemJ6UJabEV30w6/FSKxba2W5H6O2zMYMdzV5b12xYC5+dVvhBCKDBknFT3Xts5DAhu44oFT7nQRYI9MFrJBuVZDVC4NzhcCM4+lVV1CHICwPvUN26CYUED71JZXMmSahrMKhz8ZgpusTdtXJJq70WiIAUH5gjiq7TLvu+wFazQ2luICPSw79aX2irgaB5dGoU5dMT/Sg8OYEFbikkSFAoX4k0ym3uUlYH5uSaPseJlbhVhMdSMfeq34idHtFhcJIuYEd//NK0w76oOXqE+8t+i6b2PBVemuIE2kFljIHM/wBKH12gVw11GVVnCH8X2qx0WqCeprYwOvX3oLxK+Lx3EhSBjESKfYXB8i3HP0/aSfgFLCYJ5QRbfnbcqby+nWprdrGDUiocGJFPtaPe+DH7TTLn70kGScly5qighszUTbQNymQehqHU6Zw3qHWB2robaYOR1qQ0RZcXHJ3qpLNztVimrbEk8VXiz1XipVttgiT8qG8NKljnBWljUuylj+FTBPvRWm8YK2blprS3FbPqwQeJGPYVe/B/wWNSoZb24MPUo6e8dY+VaT4j+C7dvTHc03LZEOAAWXmCO4qGsiXAWVKtUOhjiqHwLWHT6QuoxcwCeR3j5GsxrL0/4hO4n37VZalytkW93WY4rNXSQfal2vNUydMuScNIUGwMzcobUv6pMn964ek024c5+ldN09gabiyTOZlDKnp+9DOcCivM/wAOIHz60GTTISxXCa4KVcarKqaa6iyabRuntRXOMBc0SUscDFPtKCDzSUc13TgRzQibJgESus8Ee1SKJIJ69qguD0jHWjtLeICgYx1qjrCyu2JuomwQB3mp7mrVvTEzyTUN+2WPOQJqDRodxqIBEq2JwMBPvQSIEU6xY9RP8NSNpCASTnpT008JJaSTkCoxCIBXBhmSEJ4ku0R3qC3ZgSSKK8XtDcBuz2qBfUQnTrRGH7AhPH3myNtuREcdKVyWbdUtoByFHAFQWXUMwY9eaEjm3JS2rSlhJjNE6hthECR1oO9aIb0/Sui/9/eqkTBXAxKP0dhity6rbdv60XofE2G2cqeaF05KooIw/Joq7p9jAiNval3wbO6eyZpF7YLTz3Ky1viCj0lZnMigPHLiNYXYQCTkcEfSg3f1SDI6d6h1tobwZwVqtOiGkHqi1dqc4OEDdy5b01NS6oRJJOMiYFV+pYCQT8qOJgLubarDP0qP+7d4FzcCpOKaaWgyUq/E8Wv8e6E1BKlc4inaG7JAk55iovFLhLFe1S6S0qAM1FP4XzS4MvgZKwa4QwHMZzUes1SXBgAH2/Wh0YXJZiccD2qMqDubaQOlDDBN8wjOcSLZIo6b0kpPuKJ8PQkKoHqJgDvNR+DHPUz0rUaO0FNt9sgMD7gg0vXq4PtN0ajs5qjE20BbDS63+z2VaPKZY3LwTHywZ/nVV4l8SPfZgxlJmOuBH+9B/FvjIvOscARVMuoWCQRjFBrPxiGE4dNJXbJRDYNUDFqN3JD+NXS38qobt3GRxRviLkjBqodzPc0zQpw2FTaakvlRu8nPFLd24pXLZJkA/KidJoywn3pokNElJtY57oCCJ9GfkKHJqR7kqF7Sfqago4CXK6tJqQNcY1K7ROtDNWFm4R8zQdtYouwpM4maE+NURgOQSunn5VzTLJI4xUd64ZiOKVkE5iuiy4OhFukAQZxV3Y8PtPYd9xDIogdyeaoSTwwiKt9JqZt7SMT+1AqS0IrAHlBacRcaeAKciJuJEjp9a7bWbjkcdKhIhcfWq5+iLkMt65qb5/Dgweak1N38AHHWhnTiiLum2m2ZmRVoAhRLiD090FrPVdM9BUmlABZ+wrviJAZvekixagcsaJmwdAhgQ49SpPDASQB2NROqLuJyScHpR9nTtZG8noaqLmYPczVWHE4kZK1SWNAIvfNT2Lm1pBn2NFai+rKDtAeYx1FBbIg0XbsFiO08/audEyqMxGw1Vhqt5a2qcQMGrDUsAkOp3dYqTXfD9x4ZDJA/DwYj8XyqivrdH4pBGDJ5/rSbCyqBhIt3T9QPok4mm/ZP/tIBKxjpRN9eN0YXpVXbwWJ5OBV1o9WrKzFR6QBB60WoMNwEGlNR0EwqrxCH2hQWxxV14X4BeZFKgDuCaj0XiW19y2kO3MjMjsaK1erdzuttBaMDAHtQaj6kBrRA3m/snaNCkJc4ydw/vNBf/Zl8s2/DH8MGQT2Pag/Fvh/UpbLsoAHIBmtP4feuEFnYgAQT2PSmNefazEyB9eaGNqrh33QY4KzdioOxAYu/CfdY2yYERzEn2qV9SAoULia0ck23gKZxEfrQHh2gnoGIb8P2pkVgZJGSXqbKacAOzG701QIuRBAI7Va2fFSwHZasdB4dcF4IUEcewkd4rmq8CZNTZtcgqZIHMHFCdUon8yMiRBnIeg91DRXaYZIymRGZjPXjuVL4hqCx9IqXQadmVpxH61rrHw81tw+xWA6fSO31oBNK9veGXkkiOxMxSw2thbhZGibGyHHLnHosnrPDXG1jwa7qPDvLYAjr/Kau7uieFJYnHB6Z/pQniLuzwV+R9wK0W1BJbOUrMNI/a8NzjtIVbqLOy5tBkThhxUN62/MQDMe+YrV+D3tKumZNTaY3DbuC0yni7I2E54wR1x0zh3j1zTeRpPJDbvK/xQymA8idp6gndxiI966QFwDi4ADU+y81JptI1yn1nropAVypdMkmuJi64XsiAJqaw4B5Ipti2cgCpdJoHOTxQHEAXTLAZsoTbh/V16/OrP8AuxiFIYT29q6/hjutFaPSBACSZHvNBdXiCDdFFAuJBFl3xTSAberNEn5CuCyFX3pai4Sw9h/tXLpwM0Nz3PiVdrBTsPOpug7DwGkdTmnomAPbNQCSse/86sLPI/Wal9rqWfcgrkGKdpxLD2FPe2T6ees/OpNDpiCf+01xcMKgNMhUuuy31ou8oHlz84rn9kZnUAE5MAAkn6CivEfDrvmAeW4gZBUgj6GjlwESYF0JoMOIG79JmoZrnJMdKG/s5xWl0Xg7MFIU5o7TeF2VurbvubTPGwlZVix2gSOM8npNKt2hs4Wpp2ymMdQ9Vl00vcSKViyy3FXpOPcTXq2j+ADHruIo/wAsuf1gVaW/g3SrBcNcIyNzbRPyWKI1tY5tgcfjNAfU2Vn4vk8AfeywC3JkCVYGIJPTr7Cq/W+HlyWPK4O3I+de0HR2mUqUUbl2kwJjpk5xVAnhVu0dpBx+vvWdtTjscOznyO2S0tm2mntbS1wy038ehXkVzwu45UBCAvJjmaJuptDhemM16pcW3kosnpWPb4VuEszOAWMmh0v5JtT8/tjLipfssD7Bc/Fv0sz8O6UEu7ZIxFGJpGbhCIOYq0+HPDwy3EDDeLpH9KvdP4W6ADcOc9ZNWr7YGvde9s5RaNJv0mg+c+KqNO4Oj1HeVE9+apr27yT5ak/xVsLfghNi5bJAJcHHYVJ4b4YtpNpAM0N210mgYb8O65tN7i6dbT0CyHhIMglIp+p07rdQ2mAbMmMQeleh6Pw22MlB7CpDprY/J+nFKH+TGMkNRg1mHA7TULG+Dau+rOtyHyCDEdKtj4g5higkcHtV0LKD8tRpp0ml37Qx7i7AiNcxog+qqv71ftUb68kZUVY37CgSB1oS5ZE8c1dmB2QUGoGmyr77SPw4qo1WmY5A6zWivWgOKGuJNPUmub+KE/aGn8gsrd0rYkEZmaHv2bjRAkDA/eta9leJg9jQty2Uxs+vejt2g7kF1JjryY7rysim08mka9CvNplF6MEZoVqu9LY9KiO09zNDqugItKmXuso9E+SferkAhJ78UFY0ZDRiJmtDY05jJGB14rPr1ADIWns+zuIIQWmYjHeptRYIT3PIolLXU9Kl1CDb86VNQYk3/wCN9plZt7R5/XtR/hOgF5igdRiTumc+wGeKjuWpgbpg8daDXUtp76XreGQyPccEfIjH1p2mQ4wVnVabmiRb19FoF+FLywots/r5UErt5mTWgt/CbsINsL7sy/yJP6VpvDtcL1tLimVdQw+v8+lHJmrnZWuMknvCW/8AYVGDCGt42/uFlNJ8Fw0s6DsApaPvto1fg+yTJZz0MbVH2g/vWlNr0yOnI6jsfcUlozdmpAzHeSl3bftJEYrcAB+lQ6X4S0qEEWpI4JZv2Birq34dbERbWQIB2gmPmRNEgVDqvELVoTcdUH+YgfvRYY0JbE9zsySqTxvw4238xB6XyRMQ3X78/esn8YaLzdP5gGbfq/08N/I/StZrfibS3VNpd9zdiUU7Qeh3tC/rQXkwCrgQRBB7Edj7V5zbcNGuKtMjf8jrp1zXqtgD6uzYKgIItJ1Gh6a9Ed8CfEB1OnG9vXb9NyeuMMfYj9QaM1nxLYU7Um6/RbSlz/8ALgfevItDf/seqe26i4isAykfit8qR3aD9xXsGj1ClQUjaQCCuAQRg4rY+s6BGUWP9RCx37Kxri4zM5aTzv2QDajW3SCESynXcd9z5bQNoPzJprIQMszN1ZjJ+3Aq0vXBE/rWfbxS3cZ0tMLjJG6DhZmJP0NJbSGvYceXkHdmmtmxB4+mOn9nTqj7NrBacRA+feuHRhhtM56TXbWr2gwRLLkHgVWO9wmN0D/ma821rzN4W+2mSUH8M6JB5kD1LeYNnPOP3q/u2xtg4oHQaEWix5D5nuaMGuUKIYAnqRNXrvNSpiF1QMgWRWl0JmYPH3qS1plk4iO/WhbHihMgseMT/KKbc1RfJwBifeliyoSZVcLyUZcU89afbYntNVX9rfgxtOAQZoN9aQSBM/OBjrVxs7nWsu+na6u2aJEVClxWBkER361WjXu5ExxzkD2+dD3tTAksQQenSiNoujDPZdgbElW15wJxHX6UHdvg9vaO1AXL7MJnj/k0Jqt5OYAOcUzTpnUqC1kWVg5DAxzQob8vJ70BZvFTlsnvUa6idwIAJ6z+tN4DBG7ogENkEa+cUTqHAy3NDu8xtOPnQTaqBk8frQz6gTn6Uwxjt6G9zPLLA0gKsF0qdzXRZWJCmt/6gWEKDkGtongZrQadtoUe1CMgCjaIbqZ/lR3hZZSCYYe4z8waWrOxCU/stMMdGcx54EVZgAn3o7TuWB7f8xS1KqxgCFPEjjFFeEajy/SQCDPSMnE5rOqOlsgX3LWY3C/DouXGnAgiOlMiRk47nGaIiJE7SCDOIiDz7U28yuAAQT3GR9+nWhA7lcmM0Pbs5wKr/GNAxUtH4Bu+Y6x37/SrvSyFMlTtPE9+P3qZLZaZE4k/yFSKxY6R54FBpNqtg2lRf/TbxeN2mY/57f8A/S/z+9elW7oUSSAPfA+5rw7xJG0eoDWSf47Z/h5BUz249wRWx8G8KOstJev3fMJmdzMwGePLEKCPnWr9U4A5omV56psrTUIe7DGdifYarWaz4u0qHaH81h+W0DcP3GB96p9V8X3WMWrAWePOaSf/AMdv+tWGi8AsqIJZh/CDsX7L/WrbTaZLY9CKvyAH3PJqmOs7cPUrsGy08gXc7D5Wa0+k1+pBN27cQRO1YsjmOB6yM0xfgOw7BrzO0D8KswB92Ync36Vq/NKmR/wHBH1BI+tR+dVWtwuJJJ6+Bc6sXNwgADgP3mnabTpbACKBAj3ge5zVZ4/pVkXju9OCB1k4JH6fWjWvxyaD1HitsgqB5gOCeEzzLHn/AEzQq+A0y1xgIuyGqKwewEnvbzevPPjvREFNSowPQ3QkcrP6j6irX4C8WuG09uNyoRtJP4d0kqesdR8zVt4n4d56vaiVKwCW69DHWDFY/wCEn/s+p8t8LeHlsOzg+g/Pd6f9VK7LVNTZixv5Ny5aftaG102s2gPf+Ls+eXtB7lazReIf2l7iO87CRHCx8utZfwC2NLrDan0OTbP3m2fnMD/Ua1+m06q7FZ2n5bveRVH8R+HK2pUg+Wp2sWJAMg8jdEnFA2es1z3tJMOb6j9o+10i1jHNF2m2lvIWit6n8MKOOTjHeKYfEpJWV3HAjJM1I2rsurEOqlASTG4CRjOIE9IMk4of+9rVwFbalrjRB2wScdEj04JyesnjCLKRcfwPr/kdU06s3h1RxvMq7WBYHAERmDwZzweKHLqEbcHA52rtZhx0JH6VWeJ+Jo0B7lkAfws27cDgqxUxwMjn5ZIXh+qsXbhQOQgUs3qYlwvSSIXnnoJNGZsjoOKRvtl5wBQf/Jacte/ZXOldbib13IDKgtyWmMRiB1P07wV5DSBvBxBA6nqag8G8SS/bLoq7ZKL0AC4AA6DE/I0S1xo9KqBBOcGR2npQqphxaBEWvE9fhGpFzmhxMz2884IdhB2hg0GIB/rTdUqbST3gAY9q5bsAne9ueJ2iCD259uab/at7wE22zMHEj71wmbaZ5eeiJrCGW5eUldkgcS3tiBUotnaZ/EwmOn3pa1YIZSWOPTPMfLiaC11xiQSkAflmfeKMBjiI86qpAATDrHK4BOYxQ9wk+okqJAg4BIPE1NptdeYwEgHPciR096ZZvGCrLxMSpyaOBGg6FDIDsp7IfXANhd2Os8/Xtigbd2TBjtPM0TdJYsY29+gMdqhubQAB0P8AwUzTsISVRpJnuobpjBMn9MUNcBOefkandckwT+woV0PRSKYZBzStRpGQt5zCr7djfjnE8fpRi2XFv1JCzHY/1qIW2PXA7H964l8YkF575M4zjpRnEnJc2G529j7lSW7amQBx0/nRthWK7ds9Mcgc03T3DbKzbhfzH/f+VTpfBMr6h3x1xQHknSybpsb11sd/HPsU21bdSNxkEkif+e9S3bW/mBPv2ogKmwsSEbfAkiNvUimq1v1I3mMCeVHX27n+tBLyb68kXC0CPMsj23KNlORJInPYjvNPF0tK2VBgepjgHmOea60MAFQiBBlufeO9TjRkiGAYAZgyR7ftUOcBmpY0n8R18+ChZnYVQGfnBIiSZ46VoNAWCkbYMwZ9qB0mnEkEfMSYg9sxOKPDGICtJ/CczHBjpE5pau4Osj02YblU/wAVeHlrBuQJQ7scxgXB+oP+mgvgfxjyrvlMfRcOPZ+Afrx9q19vSzbYXPUMwIBI3SCG9s15dqNObVx7TcoY+Y/KftBp3+Pq4muZOXt57rN2+n9wc7W3X/PZe32btEi72rH/AA148t2xNxgHSFbu38LADJJA6dQasr3iF1gTbTYo/PcGTP8ADb/mSPlTNSsyn+RhZtPZalQ4WifZXN64FBZmAA5LEAD6mq3U+Kf9NSR/GwKr9PzN9BB71Bbskp5hLXLg4uNDBSOdtvhfoKQ05uS5YkyCeAD3gGsup/IzIbYZSfj5vwWrQ/jGgzUMrE/Gmp1JubGutsZQQFGwdiI65HfqK1PwJrPM06+ncUBtsuDleDB7rBoT4u8M/wAJbgDRbaCSBjdzx0nbVP8ABN/ZqmtE4uCR/wByf1Un7UwWt2vYSdRO7Mf0lnPOz7bH/Ux0Bt6Fbq0lsoxIKtJA5Ee096wfxZpdl1ipyQHU9m5++4V6Be0ThpbaV5AAxBzPOTWa+JvDLl91NtOPSZ2rycEZyM9Y5FZ38dWDK04rEamw3X5hP7a0Po2uZnf5ZTaz4jW3ZTVC2zG5t4MbSyksD9QwrN6/4klhcbSJLcNdZmx7LjHyrT/CnhvkoyNcVxulRC4P5gvqJb5wBzzTtR8JLfum9cTznaNqsxHpyFCqo9XXk59uKNTds1Oo4OFpIBBIPARYAXvJVKhqupi8GBII3Z6EnkBzWJ1XxXcfatsJb4O23bGWIiRMkn+tDaW9rHJe3bvvuBG6GCncIMngj+lbFdDprF0PbsqWWdzGYD8bVBiY7iBxE0FrdUSzdvfOBHXtjinxVa3/AI2Rb/t7WM+qTbSLmy50zu+T8LJvotUCF8k7jgADcSZiMHmelWXhvw/qbllrnpHqZSpba3pJVlA4OQZ+Va21aXSWjdu/+oRgdUB6Qf8A3G//AFHuTtp/h7xxdzWb21A9wvbaJUFzlCcEZyDME8xUvr1XNP02iRn8C/fdlmubRpBwL3GDl82GWYG/PJVvhHiL6J8rutk/4iESR03r2YD7xXox0+9Q+4m2eDtDTgcARWU+PLCrsYIySp/FGQODirz4e1r2tNZS4v8A7YCiJZiFBxAx25rN20/VpsrsFzY5eWPpnkm9lxU3upTIGXn6+Vaay35dqQ34eIEDmOmZobV3luKpW2ASfymD7ziM0TbtNcdWZLSqx9Unbxxzx86E0vqvTsVbKgyN4JnoSTyPYVmsgZ5i+Y10zF5T0x0VZqdOqlnHpb+BsiOtTW76EsGg4hYghcCemYj9ab4l4irOoULuAJkQSc9SKF86VIIIkx6TOSIyO3vTwY5zRiHr5uVpAPn+eq7cTdm3dJaO20CP5Uy3auKAxIb0wNzZEnJ+x596lu2fyNcRSDjaeSehgdo+tQ6kQk+Z1AMANxjcN3firgzAHt+wFJIzKr/EbxYjaFWMQDP2oG3ppIO4g8x3P+1TLAeeZ4PBH0/lUF0uxII3RgRgkd6fY2BAWfUqBxxG508z9DuzskLoUnctwmCCQR+3y/ehLt6SeY6cn9TRN7SqG2nBj8pY5GMyR/wUMfEDJEKYP/P2ozGgmWifRLVQWiHkAd+5sh9I5UHdiOJHUfvzXUABLbnkgxAx0/SnFTI2ZnHqPJ/pwKnsetY2gMDjnPYAVdx1VmNkYZyy8ncpSLkKtx/TtgHEZP5vnFTHQIuAyuhO64EENMRE1ElksSHBSMTjnttNTiyWHl7hEek8e/SgExkY5Zc001ouImcpN/PdFaLUWgvlqjohBHRsjrJ4PeoiVcsVMdd0EAHE4HU0z+xQYQ22K5bscSfmZovzVKhSZht2JHTggcigkAGWyfO/dFAJbBjz2QOldSd1xmCccGD0knuJo3VIELeRcknE5g4zBrtsliAS2052nHTbxEipFv8AkvuUCJ4YEiZkEfUVDiSbdtPbNVY21z115+ZcVHpt7ABUNxyJZVb8E4G48/8Amrm4fRt2nci+rcxlccxERxQ2r1Odw/E3LINqnsDGYmKm1lhF2BXdiwk5JVjA9IPX60q92IibTz9Y00GXHif8ZlTLpjbUkliY42zLETAAz9ayHxtozvS/ET6GkbTOWUx8t2fatReFxACrAndtjcdx2iTgScR+lN8Y8MN+0VKsLhBZJMgx6oyO+P8AVV9mq/TqB7iI15enfggbTT+rRLRnmOYusV8J3R/alV+Gwp7NGPvkfWvR01EsykqoDDkTngivKWUiGXDAgg+4MivUtBrfNt2rqBoaC0CQuD2/NMj6U/8AytEFgqRcW6ee6zP4vaYqFhNjccx8j27J9V5jS7EIPwAY+Z3dRVgXtASLaEyBLP6lHMgd8e1Vl7wkXJcvKExBDSciREwRINF3RatOiekPtyQSSOwz+IY71hPDDAaTyE6co6/3B2zuH7QWpvrds31AUyDEOWmBII75FY3wjSXXvW7qIxVGkkKxBGQVEDJIJHtOa1es8f0toFQfMeSSVEEkzMnAjpgmO1D6ZDdRDp7V1gygKpvgKpUkMBbG1guD1iM4rV2Z1ShTcMMBx1MDKDmR04Tuvk7X9KtVaQ78ReL5frfxtxRXiHxA0bPKuox2gM7m1EHIACg5EdT7RTG8S2hg6bVHGy2T7epsQueCftiRvE7C6RRuQNeaGLKW9I/ys271DPr98DrVVam/dAtNdOMm84eIyWLQNqAZmopbMywa2RvFxPDPpmrv2gtBORN+IHx1Ws0/ibOVKBV2jcX4IAwWkzsGR+HqepOSbrF2ANzfbYEFibouMT03hR5S+0yev+WoslSFCPCKQeGVrrDi4xjCCTtSeDJyTVm143ILjfA/MW2gfpHTg1NakGOinY3E6g7hPuCOCFTl4l4JGcZW3nPXTum+L6cvBcjkyfMWc59TNJY46ULptElgC/cZTjdbUzCj/qvuAniVxnngCbDTIqDzSoH/AE1WfWQeSSZ2CPrx3rBfE/ivnFrSH07iXP8AGxMkD/KD+vyqNmpVJweAdzfdxzRa1SkG44tu3ndyGvULZDS6fU27b3CzC4hcGQIkkAkk9vbmaxHxN4P5D7Z3I2VPSM0T8K+JpC2LoG4elNxIVxPpEjhxJA74HIyf8Y2kFu24QqSxgEofTHdM8jqcTVqbKmzVwwklpmN28RxGvqqvdTr0cY/IC/nLLgqUa06ldLZdgSGNok9RuWJP/YQJr0HxVrhdUVJQACEIGRBChT6omMyJrIfD1hTpdufMu3GdWFvzIVSFMCCR+AmRWsG+B+EsBO4oFiceqfSZHXJGcCl9scC8QIDS63EnPvbpqmNiZhGKZJj2tN545DNTtpGZh5vqA/IISCIglgTJ+3yoTW6TepDgr+bABhc4yQSeM9h0o4XSzKHG0D8HomGwCTkckYJjkYqParJtuutkDLbFffdBxtYFuJHWkGve2P1NuQGfGAeO9NlxGfl+/ZV13wq+QfKVACZ44zjacxzx7UrVkIoe4IMjcZ2ndETgGa7qL91ncIbiosydpWCNvAiYjqPuKbqbaAbmghiCGcviYYelTkZ56daYGKADruBlSAZIHumeICXDNBAElMKekEtAAP61T6gk3Ja0UUQQEIxz3Jk9+uavX8NtlTcm2DjdLTwMuIXJP4uetUl3RpdYs87wfSm78AU8FZkz86Ns729rcvVDe2bxlp56QM4TvENSh3+m2OJggRGCJzietAW0FsGTaJJ6HMAg7SYmfYe9WGstBJtqWQieVnlZJWBIGRx0qsfVIACjl15kq6k9CCpOeOaapD7QGzHmuiHUjFfQW8tvUzsCuJl8lmk8GO2OJNVF62hA9YBzP4jOee32qddRuubQ+COeRBB4BGM96ju3ba4YF+YIJjnjK/X60yxhaknP1IHf4Pyh08PuHIUnM9xE9f4cUYLSlQZWCcHkAjnHTjPzqFfFbzMyyx24+0gGe9O/vRmXYQdpJ3e88iY/3q5FQ5x0Q2Gi24m+/f09USNcd3qO6OS5EfXkx96ks32LTuAAyAhnPGDHTOPagbN0yFVDM4KnPyiitTp2Ei6vHOwLuRDnqPpMGhua0W+Pb/E02q5zZBmO3U5o2xYuuxYJK8sSQxkkdupM1HqVYuGPpE4Ibkfbmmr/AB7lFkwSMyOxM/sB8uaM02qfyxDqVW71BIIMYAzsge8UEyDI5eZ3RmFrhF9+nkbvlR6N33EHzSh/EASNw+Z4n2mixqWkeSgS2x4YsxBGMyM9/r93eN6BGa2nmlwCXY2lXAnjPP8AziovDlazcIuFwpwZIkKeR1E9YnpQCWvbiAvGUET3zPT3Vm4muGfP17dUfobDDoBkxHrkETMDkZ/SiLChllSWjJHqXiJgj3HT7ULdvL5m63vAUBQxxzgkvMqASp6YHyqW5oSALu8OZIIuT6oIyJ4EzziOetLumZcYn33eFMB5ECFaHX23ueYLQZlghiDg7dpOMDmMx/OpL+qBWGuBHyBKGekGQwEHPUzQnhWmS8GhWRiI3LcgKyiZ2qSODTHtWvVtuAtu2hQzZKxkK0DtkdT3pbAzFhva2/5spaGzHnt5losL4vpwly4qmVDHae46Vb/BuujT3UYE+U0gAwYfj6BpJ9hQfxAkXPxOxK+ovzIJHbiAKr/BdYLN8zlHUqw79f6/evUNH1tnAOo89s15Z5NDaSW/9XT0z9ltdddsBRuu7lGQATlg0kFeSvTBERU6eO6ELJ06bmEMWgfoSYFYDxDwzUNdchLjKWYqxmCs4IJxEEcVX63RvZ2+YpXdxMZjnjjkVnt/j6VRrQXmc7GOOQjJatfa6kmRbK8+frmt8NfYvXP8FNJbVBLNskxxCoGlz9vciodX4/YUbbZRARELJYKCGhgvpDFtxI96wNnW7JK8kEfeK7ZuFyFVWZj26mm2bBTY2Iy4pR22OLrHkPjVabWeO226u30/qRSXx5Uti3ZQS4m4X5aDhfSRCDBjqcngRUDwnUbWby4CiTmT+Ip0xyrH5CjND8Nm7nz1UjGAMYmCC4YH2jIyJFWBo02kzbLf7KC2tVcBhJOe73T28av9HC/9oA/U5oPU+JO347zH2Ln9pqbxX4WuWQTu3wNx4BA9gWJOM4ERVLo9MGu20PDMAavTFNwxNMjgqVTUYYcL8VuvAbZOmZbm8ITypG4KygyJOB/Wm2vhtLk+SlxcSN+0AjEGSRBzwR9RWs8L0iqhZ28vIKkqFJ2ngAmTJGDEU+4tosHQmYJDbmWROSVkmPcx7SKwhtr21HFkiecTysD512H0KL24HCYtMZa5ry7xHR8qwyPp+/FEeIazUarT2oFy41vcj7QWgrtCnAkSGH1mrT4qsqrgrG1hIiepIPJPb9elN+DrTlXZWCxdmS4QgBQsgEZ6j7Vs1KwNEVgBvvpmFjMo4azqRJjK2eh84K60OmGmt2nLgAqqhXRmgDqegOTKxiKP1HjdhQ4VpuYjy02oTGZaOI/ajdLpzsbf65AkqqsQT+dT5nq4WMc9KprmlLOAtoZ5S6qJAGAFYqCSRBn/AHrCaadV5c6bayAIz1b6+uU7bnuBABFt4N+3wru5q2ewXK2/WogHzHDE8zuUErAyZ6cUB4loXS2ANsQWOxdqkk/liFlcZ613TWMMNgsghlI87eRM8BSFBx1j8PXmg/Jf1lDdUx63uIZIAkn0rKzEnOaFTaKbvtIz1g8BcEjsTJytBEsbjv8AI91Lf8U8td7W5bAlf8NjOI6iIAaQcluoFQrrmf0FVvY/BdYFl7bWkASDkda5au7lJvoCzQF3rukd4DSpmAYziIqLS2GV3AVI/iQopEAkgNtlPl7c9KaFOmGmRcazbobRHTic1V7KjXQBbrPHyCU++t1xNsBEAkomyR2BAbI4zPSlqNUIV2ubY9MKSQ4AxASSWJM+rMDpzUfiijad0oBBPrd8QxYMYEngD378VEDZNndZ3QSC8K0jaTDcggiTx34zUtaC0Ei3AD1MnPqrusbGDnBseJ8KHvXjdlbdwzGd6i2ATyQxckY6Cg77W/ST6roy0TBC+xmePaRU3idny+WB3MChlmJJEevB2DE985HYe94h6dllVVcqTB3uIklpmRgzHePem6TbDDlvy7nXgAM8zuXqVHNkTJ3f1l1mwuobrzu25X8u094P4TAPafautauMSyWhtP8AGE56wZE88/KogoaScE4BtAmBOJAGRxyelSPp4J4bMTtU8AYMAxE96MYbl6hCplzhP7CrbBUsPNdiG4IPXMCPnFJbpBUMjHbhSvpOCTLBpHXk0XZIQbmGR1Z14JzI7/1rts+aQQnHJ2tx2k4IxNGLs7W8yQhTsBiE8j6kg63yUjX43F5JInMFsRkgY47H6U3TTcDeraVGCIIPse3zzFK87kRhc4JPJH+Y5MVD/aAwhiN4GY4ycY6njPy71QNtb5/pWxQ77j+kWnoBBu3SpBMSCNw4MBZAz3x9KN0egtEM43kiWM8iMj0oOOcntJqsVZG1AWaQQOmJkgHK46e9FeFSilhKqMuwU72gcDdiAPc0OoCBY384dkYVJP2i1+/DTydyNta+3BKAEEDhoIORkRweOvSh9YSLRe2NrFlC5ByWUHMCBmJNDLdS4JdlSWnj04nAYYXHfHyovSWUursG0iPSgTEZ5LDLHoAJnrVMIZfvM+fCn6jntiRlaM/XJWGk1kmLn+GQQSriM4BHWRnnrg+1H3QijYGUNkSrFQJAXaGblsnPp7e1BafW2VVLboWdULLAZ3XJES5wDgxPBrmh8Qt3VFsIBcX1QbZQEL/lEicmR78Um5pmYIAz3cDy6zx1TbHiPuN/P8jsjfDbAgM5fzOQ7WzbUeqIIBIBMwCOSeOlPtavYGRtrQYBVShKmdoMfhYNHq4IINVur8Vu/hcMhEhbduW44A6E4n5joRRNrxlChD7uQTvlduBn0iRk++eIxVTSfGJwnlpyMZcAYVW1Q50THnuqL4ltbfLeWl90hjugKRHqnMyazOqbr2rRfFCx5Zx6txwGxBHp9QHBY8Y+RkVm7ma3NlJ+kOvuVg7YAa7unsFpvAvE2uKoN+0hRdhDkqSgHRyYAOAQMjbPaqr4qvKSii55jCSxDblWYhQeD1yI6VTOlP2VzNnaypjHaB7+yvU2yo+j9N3fllwT9DoGuMFUST06Adye0Zrb+FaG3pQCXsb+HFy4E+UAgSvseee0Y3Tae7ckIHYcGJjGYPT70da+Gr5yyhPdjzPUFQd1D2puMYS8NG7wyrbI80/uYwuO/Qf7vngtcur0m51N5CrmCN1sDbljwGGGdoM5qa1qdKbqsLlktt2soMI4XC7oJEiAQQcR71lW+FrqxLW5PAlpPXHpzjP3pN8NPt3+gjEQ0lp6j0wfuD7UmNnom/1Df/NU6a+0Fv8AxT6+y2PxD4kLdiC+nllICIxdpggQcwuZOYxXmmUKuvKkMPoZq4veBPb5x+oP1H9KHv6UryOevQ01sdFtFsNdMnPyfUpHaqr6pGIRGXnZeg+Ea+zqLRc+tW2+lnb0sBG2B/6Y95HAME1Je1aNckNtBGzaqkgCCI9QgyO/T5xXmej1VzTP5lo/9y/lYdiK3v8AfrXLdprf+IrSUSGZgeHTaARieZWAetI7RsP0zjZcHjl3BGl8jx0Tux7WHnA8X6wfg+n7pfjd0RwqqybUEhwAZlu3IjbHtirH4e0w8hLJ2gj13CwYFN/qlSYVcECTiJOeKo/G9BeZ2e6FQEgLlUUhMYzAELB7dularSaghEZLgRWUMT5sqvdCFYRJiIkdcRi9Y4dmpsDpI1nUXzvOvULtnpO+s+q4cvJ4A9bK8vXjZRfIEAKJbaLrwxMRMyPYDtntSalNQ5bc6Xg3q3u5QzEbVO4bDz6YjuKZe8RId9thbkgENbg8HJJQsQIA5mf8tF6Pxx0PmXrSqfKIUISyNwVZoUtbIA4YHB9hSDKVSm36jWgk62JOogzi6AjeRAkdUewuwix5QfWx69M1RpeVG9LsjcbSHO5QTAYIPV1HP1pr3LYlybiNwoXzFSTgyPNlhAgjB4xV3qfHUYEPYtyfSbgK2wMfwuWBM94nGKE1N1r0WzcwTlZC8jmGUqx/+PfmmhUxXc0jfcG3RvvB4gWV2UXwYgEf/Jzz3n0/sD6fVW4htSbeSNwXBIBiLrRtSCPSSeeRM1BrrhJ2bHZhJwMQQIYlF9Wd2Y+tR+KahICC0QswGIFuNsSRJEzjP9a4unFxt6pdUAZlp2kQIDbjI+sgd6I2m1pxGRzjPmIN7566qxxn8SNJuT7g9/1cOWGQhUdmOSwAO1x03PaJz8jx068s+H+cdpty4AAc5BH5gRtGAZ6D5VM11N0lfRPqubWO4yBIYN6YkDPBHWYqG5a6WV/ww0N5YDFzBA3MpALAGcHE1zZGVjvPrr6ADjChxBs4T2z5RPWx4nI9tC2FCEyRwwXBU/Uen/NPT3qq1tn1naDIxk4YERO3gj2maOXTf4jhDaQcTtJ/CcSDG0jmZ+UGu3FUP67rtKlQCVwSNsrMZzOOfeKI04TI3eZD98lZ0vbh08Gpn0VEAVwNgJEmH5HTDD2n6TT7jxyVbJglWP7Jj5VPf0h9IX1nruKgxMgQVn6+9MtaZhI9Yg9U/wBqalpSZxNzHsO1wkut9BUx6c4EH3C9B8zzTNFf3H0k+kcmAMcngmeePlxSpVYtAaSqNeXVGg7j6EhTXIhd1xWzKjy4gmTMwTMj9OlM1qoRucrub8IG/IkCDgDgHPsKVKhtBkXR3kQ6wyG87hqePsom1O8ht5O0RtA28iOhAn7cVJZ8XAAVrcgSRMTwckwTx0ntXKVG+m02KSqVngBwOcyrPw3SjUXVABW4RITcWQwMnJEY6f8Amp7XhIhzks0EFSQUAjcRLRnsZ+tKlWbWquZVwA2t6ytSnekXHMed+XRWLuuyPKJAALsWBZpkCZ55GPn86G1lvbb83y5AGCCFPoJGXnd06ClSoTHQJG/ed07/AGR3PODzWfhCoQGF02yDhw0g5zsc5mZnof6aXRahCNzAAKhVCJ3LjMSDBlT1jA+ZVKp2hjXMlVxQ2QPIG7msB49rDdunAUL6YGIIMme5nE9YHzqsIpUq2mABoAWBXM1Xcz7p+j0rXXW2vLGBPc1o/wC4lsMRHmsAZJgAHGVUiDz+Y/SlSpTaarhVDAbRPrHsnthpMNN1QiSP689kf4c4dPNeWTgFmIUSCQfLUQxxGQOM9KluOxdLkW46CGIgiCTx6u2IH7qlSNSz3RvI6W+Voj72AnWERZ0d7ajetmGeUMgYnJGT7yaTPAC7QP4XKgyNxLAgEEZJ5JHsaVKh0343X8z85EqSNUvGFCIocMHzsKQFEnDMu8AnaOM/h/LOa+7plVPXu3vkqSGHST0C9YAnjngUqVG2Un6bHjUmek25WU1qDXh4dos5q7O0kfarL4L1vl+cpYBVhxO7BIIMbfzEER0nmlSrWewPYQV5xjyxwcNFc3fEy5ZVR7yEDcLj7gw6HaxHbvPvUWu0t20geFFoekdAJJMbVPWOg7UqVZzmto7QKTRbEG6668+VuGS0frVHbN9WYJByA0PJC6LU3AxXeEL4yof9SCQZjPvRmq07onnNqPSAck3FICejAScDjmuUqY2oYHMwwMRg2blzidBqgUnF8l5JicydI48SpD4IWh4nfhy5LHA3bgQwJxPP27z6ZLdk+qQIYdoiTJI3SYiIA+dKlWY2s+t9rjZauztDactEZFQL4V+Np8sgxFliCTODLLgErxmPrUlmzeY7ldlQEglNm6SJBlhPQA5Jnr1pUqipWcAZg5C43hWbRaThkjkUNo7dywzPtFxWJzvKmIBnbtjdBH/nNR3dWsep/M9RgOv4TEkyB/D7nkilSpug0Vml7s5jtzn0hC/ANjdN76f3rKiWwGhNqqQAS4Wd27EAyDA+Qqu8Q1RtvtA3BAAvKwCJjbuiM/rXaVHoDE+DlE90HaT9OnibnI83IR9QsAtukn0wqiZ4BYGRHy+3SPzS3CAx32n9wKVKmg0RKzPquxYf795X/9k=" class="document-logo" style="width: auto;">
            </div>
            <div class="mt-3">
                <h3 class="font-weight-bold">
                    {{$gs->disqus}}<br/>
                </h3>

            </div>
            <div class="mt-3" style="font-size:8px;">
                    <b class="font-weight-bold" >{{__('First, Last name / Company name: ')}}</b>{{$user->company_name ?? $user->name}}

            </div>
            <div class="mt-3" style="font-size:8px;">
                <b class="font-weight-bold" >{{__('Personal Code / Company Registration No: ')}}</b>{{$user->company_reg_no ?? $user->personal_code}}
            </div>
            <div class="mt-2" style="font-size:8px;">
                <b class="font-weight-bold" >{{__('Address: ')}}</b>{{$user->company_address ?? $user->address}}<br/>
                {{$user->company_city ?? $user->city}}, {{$user->company_zipcode ?? $user->zip}}<br/>
            </div>
            <div class="mt-2 mb-3" style="font-size:8px;">
                <b class="font-weight-bold" >{{__('Email: ')}}</b>{{$user->email}}
            </div>
        </div>
    </div>
    <div class="text-center row text-wrap text-center">

        <div class="mt-3">
            <h6 class="font-weight-bold">
                {{__('Transaction History')}}<br/>
            </h6>

        </div>
        @if (isset($start_time) && isset($end_time))
        <div>
            <h6 class="font-weight-bold">
                {{$start_time ? __('FROM ') : ''}}{{$start_time ?? '' }}{{__(' TILL ')}}{{$end_time}}<br/>
            </h6>
        </div>
    </div>
    @endif
    <div class="table-responsive mb-3">
        <table class="table card-table table-vcenter text-wrap datatable justify-content-center">
            <thead>
                <tr>
                    <th style="width:15%;font-size:8px;">Date/Transaction No.</th>
                    <th style="width:15%;font-size:8px;">Sender</th>
                    <th style="width:15%;font-size:8px;">Receiver</th>
                    <th style="width:20%;font-size:8px;">Description</th>
                    <th style="width:15%;font-size:8px;">Amount</th>
                    <th style="width:10%;font-size:8px;">Fee</th>
                    <th style="width:10%;font-size:8px;">Currency</th>
                </tr>
            </thead>

            <tbody>
                @php
                    $i = 1;
                @endphp
                @foreach($trans as $tran)
                <tr>

                    <td style="font-size:8px;">{{date('d-m-Y', strtotime($tran->created_at))}} <br/> {{$tran->trnx}}</td>
                    <td style="font-size:8px;">{{__(json_decode($tran->data)->sender ?? "")}}</td>
                    <td style="font-size:8px;">{{__(json_decode($tran->data)->receiver ?? "")}}</td>
                    <td style="text-align: left; font-size:8px;">{{__(json_decode($tran->data)->description ?? "")}}<br/>{{ucwords(str_replace('_',' ',$tran->remark))}}</td>
                    <td style="text-align: left;font-size:8px;">{{$tran->type}} {{amount($tran->amount,$tran->currency->type,2)}}</td>
                    <td style="text-align: left;font-size:8px;">{{'-'}} {{amount($tran->charge,$tran->currency->type,2)}} </td>
                    <td style="text-align: left;font-size:8px;">{{$tran->currency->code}} </td>

                </tr>
                @php
                    $i++;
                @endphp
                @endforeach
            </tbody>
        </table>



    </div>
    <div id="document-des" style="text-align: center; font-size:8px;">
        The document is computer printout and does not require any additional signatures or the Financial Institution's seal.<br/>
    Monezium GE LLC registered in Georgia(Registration number: 4151104933; license number: 398/S/1B-7T/393/2021)cooperating with<br/>
    Monezium Spzoo, registered in Poland(Registration number: 0000728097 ; license number: MIP33/2019)<br/>
    Clear Junction Limited, registered in England with registered number 10266827, Registered address: 4th Floor Imperial House, 15 Kingsway, London, United Kingdom,
    Clear Junction is authorised and regulated by the Financial Conduct Authority under reference number 90068
    </div>
    <script src="{{URL::asset('assets/admin/')}}/js/jquery.min.js"></script>
    <!-- Tabler Core -->
    <script src="{{URL::asset('assets/user/')}}/js/tabler.min.js"></script>
    {{-- <script src="{{public_path('assets/user/')}}js/demo.min.js"></script> --}}
    {{-- @include('notify.alert') --}}
    @stack('script')
</body>
</html>
