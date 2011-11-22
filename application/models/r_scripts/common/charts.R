# TODO: Add comment
# 
# Author: Kamil
###############################################################################

#zx - zmienna x (pierwsza) do rysowania
#zy - zmienna y (druga) do rysowania
#etx - etykieta osi (najczęściej zmiennej) X
#ety - etykieta osi Y
#szum - w przypadku wykresu rozrzutu zapobiega nakładaniu się na siebie punktów
#wielkosc - w calach - obszaru wykresu
#dpi - rozdzielczość wykresu
#nazwa - nazwa pliku wynikowego, musi kończyć się na .png
# wiele parametrów ma wartości domyślne, nie zawsze trzeba podawać wszystkie

# wykres rozrzutu 2 zmiennych - wypasiony
# lista możliwych kolorów (trzeba podac 2 numery, drugi ważniejszy) w formacie np.: palette()[c(1, 8)])
# [1] "black"   "red"     "green3"  "blue"    "cyan"    "magenta" "yellow" 
# [8] "gray" 
# na defaulcie jest black+red
rozrzutu_lr=function(zx, zy, etx='domyślna etykieta osi X', ety='domyślna etykieta osi Y',
					wygladzanie=0.5, szumx=0, szumy=0, wielkosc=6, dpi=72, nazwa='rtu_reg.png', elipsy=FALSE,
					kolor=palette()[c(1, 2)]) {
					
	if(any(class(zx) == 'factor')) {
		legenda <- legend.factor(zx,mode="num")
		zx <- as.numeric(zx)
	} else {
		legenda <- legend(c())
	}
	
	if(any(class(zy) == 'factor')) {
		legenda <- legend.factor(zy,mode="num")
		zx <- as.numeric(zy)
	} else {
		legenda <- legend(c())
	}
	
	library(car)
	png(filename=nazwa, width=wielkosc, height=wielkosc, units='in', res=dpi)
	scatterplot(zx, zy, xlab=etx, ylab=ety, span=wygladzanie, jitter=list(x=szumx, y=szumy), ellipse=elipsy, col=kolor)
	dev.off()
}

rozrzutu=function(zx, zy, etx='domyślna etykieta osi X', ety='domyślna etykieta osi Y', szum=FALSE, wielkosc=6, dpi=72, nazwa='wykres-rozrzut.png')
{
	if(any(class(zx) == 'factor')) {
		legenda <- legend.factor(zx,mode="num")
		zx <- as.numeric(zx)
	} else {
		legenda <- legend(c())
	}
	
	if(any(class(zy) == 'factor')) {
		legenda <- legend.factor(zy,mode="num")
		zx <- as.numeric(zy)
	} else {
		legenda <- legend(c())
	}

	png(filename = nazwa, width = wielkosc, height = wielkosc, units='in', res=dpi)
	if (szum) {
		plot(jitter(zx), jitter(zy), xlab=etx, ylab=ety)	
	}
	else {
		plot(zx, zy, xlab=etx, ylab=ety)	
	}
	
	dev.off()
}
# rozrzutu(rnorm(1, 100), rnorm(1, 100))

# wykres słupkowy
slupkowy=function(zx, etx='domyślna etykieta osi X', ety='Częstość', wielkosc=6, dpi=72, nazwa='wykres-slupkowy.png')
{
	if(class(zx) == 'table') {
		if(dim(zx)[1] > dim(zx)[2]) {
			zx <- t(zx)
			lab_x <-etx
			lab_y <-ety
			legend_x = rownames(legend(colnames(zx)))
			legend_y = rownames(zx)
			legend(c())
		} else {
			legend(c())
			lab_x <-ety
			lab_y <-etx
			legend_x = rownames(legend(colnames(zx)))
			legend_y = rownames(zx)
		}
	} else {
		zx <- factor(zx)
		lab_x <- etx
		lab_y <- ety
		legend_x = rownames(legend.factor(zx))
		legend_y = c()
		zx <- xtabs(~zx)
	}
	
	if(length(legend_y) > 0) {
		png(filename = nazwa, width = wielkosc, height = wielkosc, units='in', res=dpi)
		barplot(zx, ylab=lab_y, xlab=lab_x,names.arg=legend_x,legend.text=legend_y)
	} else {
		png(filename = nazwa, width = wielkosc, height = wielkosc, units='in', res=dpi)
		barplot(zx, ylab=lab_y, xlab=lab_x,names.arg=legend_x)
	}
	dev.off()
}

# wykres słupkowy średnich
slupkowy.srednie=function(tab_srednie, etgroup='domyślna etykieta osi X', etresponse='Średnia', wielkosc=6, dpi=72, nazwa='wykres-slupkowy-srednie.png',emptyLegend=FALSE)
{
	png(filename=nazwa, width=wielkosc, height=wielkosc, units='in', res=dpi)
	barplot(tab_srednie, xlab=etgroup, ylab=etresponse, names.arg=rownames(legend.srednie(tab_srednie,addEmpty=emptyLegend)))
	dev.off()
}

# histogram
histogram=function(zx, etx='domyślna etykieta osi X', ety='Częstość', wielkosc=6, dpi=72, nazwa='wykres-histogram.png')
{
	png(filename = nazwa, width = wielkosc, height = wielkosc, units='in', res=dpi)
	hist(zx, ylab=ety, xlab=etx, main=NULL)
	dev.off()
}

# kolowy

kolowy=function(zx, tytul='Domyślny tytuł', wielkosc=6, dpi=72, nazwa='wykres-kolowy.png')
{
	png(filename = nazwa, width = wielkosc*1.5, height = wielkosc, units='in', res=dpi)
	pie(xtabs(~zx), main=tytul,labels=rownames(legend.factor(zx)))
	dev.off()
}

# wykres skrzynkowy
skrzynki=function(x, y, etx='domyślna etykieta osi X', ety='Częstość', wielkosc=6, dpi=72, nazwa='wykres-skrzynki.png')
{
	if (any(class(y)=='factor')) {
		zy <- factor(y) # group
		zx <- x # response
		ey <- etx # etykieta group
		ex <- ety # etykieta response
		emptyLegend = TRUE
	} else {
		zy <- factor(x)
		zx <- y
		ey <- ety
		ex <- etx
		emptyLegend = FALSE
	}
	png(filename = nazwa, width = wielkosc, height = wielkosc, units='in', res=dpi)
	boxplot(zx~zy, xlab=ex, ylab=ey, names=rownames(legend.factor(zy,addEmpty=emptyLegend)))
	dev.off()
}

# wykres poletka
poletka=function(x,y,etx='domyślna etykieta osi X', ety='domyślna etykieta osi Y', wielkosc=6,dpi=72,nazwa='wykres-poletka.png') {
	zx <- as.factor(x)
	zy <- as.factor(y)
	tab <- table(zx,zy)
	rownames(tab) <- rownames(legend.factor(x))
	colnames(tab) <- rownames(legend.factor(y))
	png(filename = nazwa, width = wielkosc, height = wielkosc, units='in', res=dpi)
	mosaicplot(tab,shade=TRUE,xlab=etx,ylab=ety,main="")
	dev.off()
}