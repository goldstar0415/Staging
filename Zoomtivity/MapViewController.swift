//
//  MapViewController.swift
//  Zoomtivity
//
//  Created by Callum Trounce on 17/02/2017.
//  Copyright © 2017 Zoomtivity. All rights reserved.
//

import UIKit
import SKMaps

class MapViewController: MainViewController {

    @IBOutlet weak var mapView: SKMapView!
    
    var currentUserLocation: CLLocation?
    
    var logoImageView: UIImageView!
    var searchButton: UIButton!
    var searchField: UITextField!
    
    
    override func viewDidLoad() {
        super.viewDidLoad()
        // Do any additional setup after loading the view, typically from a nib.
        configureMap()
        configureNavigationBar()
    }
    
    func configureNavigationBar() {
        let logoImage = UIImage.init(named: "zoomtivity_logo")
        logoImageView = UIImageView.init(image: logoImage, highlightedImage: logoImage)
        logoImageView.contentMode = .scaleAspectFit
        
        
        searchButton = UIButton()
        searchButton.setImage(UIImage.init(named: "icon_search"), for: .normal)
        searchButton.imageView?.contentMode = .scaleAspectFit
        searchButton.addTarget(self, action: #selector(MapViewController.toggleSearchButtonTouched(sender:)) , for: .touchUpInside)
        searchButton.tintColor = UIColor.white
        
        searchField = UITextField()
        searchField.backgroundColor = UIColor.white
        searchField.textColor = UIColor.darkGray
        searchField.placeholder = "City, name, etc"
        searchField.alpha = 0
        
        if let navigationBar = self.navigationController?.navigationBar {
            logoImageView.frame.origin.x = navigationBar.frame.size.width * 0.175
            logoImageView.frame.origin.y = 0
            logoImageView.frame.size.width = navigationBar.frame.size.width * 0.3
            logoImageView.frame.size.height = navigationBar.frame.size.height
            navigationBar.addSubview(logoImageView)
            
            let buttonWidth = navigationBar.frame.size.width * 0.1
            let buttonHeight = navigationBar.frame.size.height * 0.6
            
            searchButton.frame.origin.x = navigationBar.frame.size.width - buttonWidth - 5
            searchButton.frame.size.width = buttonWidth
            searchButton.frame.size.height = buttonHeight
            searchButton.center.y = navigationBar.bounds.midY
            navigationBar.addSubview(searchButton)
            
            
            let searchfieldHeight = navigationBar.frame.size.height * 0.6
            searchField.frame.origin.x = logoImageView.frame.origin.x
            searchField.frame.size.height = searchfieldHeight
            searchField.frame.size.width = 0
            searchField.center.y = navigationBar.bounds.midY
            searchField.layer.cornerRadius = searchfieldHeight / 2
            searchField.clipsToBounds = true
            searchField.font = UIFont.systemFont(ofSize: 12)
            
            let paddingView = UIView(frame: CGRect.init(x: 0, y: 0, width: 15, height: searchfieldHeight))
            searchField.leftView = paddingView
            searchField.leftViewMode = .always
            
            navigationBar.addSubview(searchField)
        }
    }

    func configureMap() {
        mapView.settings.followUserPosition = false
        mapView.settings.rotationEnabled = false
        mapView.settings.showCompass = false
        mapView.delegate = self
        mapView.mapScaleView.isHidden = true
        
    
        SKPositionerService.sharedInstance().delegate = self
        SKPositionerService.sharedInstance().startLocationUpdate()
    }
    
    func getFoodForCurrentMapPosition() {
        let northEastCoords = mapView.coordinate(for: CGPoint.init(x: mapView.bounds.size.width - 1, y: 1))
        let southWestCoords = mapView.coordinate(for: CGPoint.init(x: 1, y: mapView.bounds.size.height - 1))
        DatabaseManager.sharedDataManager.fetchPoints(type: "food",
                                                      southWestPoint: southWestCoords,
                                                      northEastPoint: northEastCoords,
                                                      completion: { points in
                                                 self.placePointsOnMap(points: points)
        })
    }
    
    // MARK: BUTTON FUNCTIONS
    
    func toggleSearchButtonTouched(sender: UIButton!) {
        
        sender.isSelected = !sender.isSelected
        
        if sender.isSelected {
            showSearch()
        } else {
            hideSearch()
        }
        
    }
    
    func showSearch() {
        searchButton.setImage(UIImage.init(named: "icon_cancel"), for: .normal)
        
        UIView.animate(withDuration: 0.25, animations: {
            self.logoImageView.alpha = 0
        }, completion: { ( completed ) in
            
            self.searchField.placeholder = "City, name, etc"
            self.searchField.alpha = 1
            
            if let navigationBar = self.navigationController?.navigationBar {
                UIView.animate(withDuration: 0.5, animations: {
                    self.searchField.frame.size.width = navigationBar.frame.size.width * 0.7
                    
                }, completion: { ( completed ) in
                    self.searchField.becomeFirstResponder()
                })
            }
        })
    }
    
    func hideSearch() {
        searchButton.setImage(UIImage.init(named: "icon_search"), for: .normal)
        
        self.searchField.placeholder = "City, name, etc"
        self.searchField.text = ""
        
        UIView.animate(withDuration: 0.5, animations: {
            
            self.searchField.frame.size.width = 0
        }, completion: { ( completed ) in
            
            UIView.animate(withDuration: 0.25, animations: {
                
                self.logoImageView.alpha = 1
                
            }, completion: { ( completed ) in
                self.searchField.resignFirstResponder()
            })
        })
        
        
    }
    
    
    
    
    
    
    
    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }


}



