# pobiera unikalne etykiety jako kombinacja liter
# mode: num, upper, lower 
getcharmap = function(x,mode="upper") {
	charmap <- c()
	i=1 # iterator
	if(mode == "num") {
		while(i <= x) {
			charmap[i] = i
			i = i + 1
		}	
	} else {
		if(mode == "upper") {
			start = 64 # pierwszy znak z utf-8 (A)
		} else {
			start = 96 # pierwszy znak z utf-8 (a)
		}
		
		n = 0 # ilość zmian pierwszej części etykiety
		z = "" # pierwsza część etykiety
		while(length(charmap) < x) {
			k = start + i - n*26
			charmap[i] <- paste(z,intToUtf8(k),sep="")
			if(i == (n + 1)*26) {
				n = n + 1
				z = charmap[n]
			}
			i = i + 1
		}
	}
	getcharmap=charmap
}

legend = function(labels,mode="upper",addEmpty=FALSE) {
	if(addEmpty == TRUE) {
		write.csv2(data.frame(), file="legenda.csv",append=TRUE)	
	}
	
	charMap <- getcharmap(length(labels),mode)
	legenda <- as.data.frame(labels)
	rownames(legenda) <- charMap
	write.csv2(legenda, file="legenda.csv",append=TRUE)
	legend = legenda
}

legend.factor = function(x,mode="upper",addEmpty=FALSE) {
	legend.factor <- legend(levels(x),mode,addEmpty)
}

legend.srednie = function(tab_srednie,mode="upper",addEmpty=FALSE) {
	names(tab_srednie) -> labels
	legenda.srednie <- legend(labels,mode,addEmpty)
}
